<?php
namespace Sentegrity\BusinessBundle\Services;

use Sentegrity\BusinessBundle\Entity\Repository\PolicyRepository;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Entity\Documents\Policy as PolicyEntity;

class Policy  extends Service
{
    /** @var PolicyRepository $repository */
    private $repository;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->repository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\Policy'
        );
    }

    /**
     * Create new policy. If no organization is provided system will auto assign it
     * to the default organization which has owner ID of 0.
     * 
     * @param array $policyData
     * @param $organizationId -> defaults to 0 if no organization is sent
     * @return \stdClass
     */
    public function create(array $policyData, $organizationUuid = "")
    {
        /**
         * $policyData template:
         * array(
         *      "name" => ...,
         *      "platform" => Platform::IOS | Platform::ANDROID,
         *      "is_default" => 0|1,
         *      "app_version" => ...,
         *      "data" => [json decoded into an array]
         * )
         */

        // data should be saved as a json string
        $data = json_encode($policyData['data']);
        // create fresh uuid for policy, use current timestamp as a seed
        $uuid = UUID::generateUuid(time());

        $organizationId = 0;
        if ($organizationUuid) {
            /** @var Organization $organizationService */
            $organizationService = $this->containerInterface->get('sentegrity_business.organization');
            $organizationId = $organizationService->getOrganizationIdByUuid($organizationUuid);
        }

        // now store data using doctrine entity manager
        $policy = new PolicyEntity();
        $policy->setUuid($uuid)
            ->setName($policyData['name'])
            ->setPlatform($policyData['platform'])
            ->setIsDefault($policyData['is_default'])
            ->setAppVersion($policyData['app_version'])
            ->setData($data)
            ->setOrganizationOwnerId($organizationId);

        $this->entityManager->persist($policy);

        return $this->flush(
            'An error occurred while saving policy. Save failed!',
            $uuid
        );
    }

    /**
     * Updates an existing policy. While updating you can not change organization owner.
     * Once owner is set it is permanent. Along with that, platform is also un-editable.
     *
     * @param array $policyData
     * @return \stdClass
     */
    public function update(array $policyData)
    {
        /**
         * In addition to policyData array from create, this one contains uuid field
         * to enables us to find current policy and update it
         */

        // data should be saved as a json string
        $data = json_encode($policyData['data']);

        $policy = $this->getPolicyByUuid($policyData['uuid']);
        $policy->setName($policyData['name'])
            ->setIsDefault($policyData['is_default'])
            ->setAppVersion($policyData['app_version'])
            ->setRevisionNo($policy->getRevisionNo() + 1)
            ->setData($data);

        $flush = $this->flush(
            'An error occurred while updating policy. Edit failed!',
            $policyData['uuid']
        );

        if ($flush->successful) {
            return new \Sentegrity\BusinessBundle\Transformers\Policy($policy);
        } else {
            return $flush;
        }
    }
    
    /**
     * Gets policy from database. This a simple read that performs read only by uuid. More
     * complex read will be used to get more specific policy. This here is mostly for admin
     * usage.
     * 
     * @param array $policyData
     * @return \Sentegrity\BusinessBundle\Transformers\Policy
     */
    public function read(array $policyData)
    {
        /**
         * This policy data contains only uuid. It is left as an array in case some other
         * data needs to be added, and also to keep consistency.
         */

        $policy = new \Sentegrity\BusinessBundle\Transformers\Policy(
            $this->getPolicyByUuid($policyData['uuid'])
        );

        return $policy;
    }

    /**
     * Deletes policy record from database by given uuid.
     *
     * @param array $policyData
     * @return \stdClass
     */
    public function delete(array $policyData)
    {
        /***/
        $policy = $this->getPolicyByUuid($policyData['uuid']);
        $this->entityManager->remove($policy);
        return $this->flush(
            'An error occurred while deleting policy. Delete failed!'
        );
    }
    
    /**
     * Gets policy from database by given uuid
     * 
     * @param $uuid
     * @return PolicyEntity
     * @throws ValidatorException
     */
    public function getPolicyByUuid($uuid)
    {
        $policy = $this->repository->getByUuid($uuid);

        if (!$policy) {
            throw new ValidatorException(
                null,
                $this->translator->trans('Policy with a given uuid not founded.'),
                ErrorCodes::NOT_FOUND
            );
        }
        
        return $policy;
    }
    
    /**
     * Gets all polices of certain organization
     * @param $uuid -> organization uuid
     * @param array $policyData
     * @return array $rsp
     */
    public function getPolicesByOrganization($uuid, array $policyData)
    {
        /**
         * $policyData template:
         * array(
         *      "offset" => ...,
         *      "limit" => ...
         * )
         */

        /** @var Organization $organizationService */
        $organizationService = $this->containerInterface->get('sentegrity_business.organization');
        if ($uuid) {
            $id = $organizationService->getOrganizationIdByUuid($uuid);
            $policies = $this->repository->getByOrganization($id, $policyData['offset'], $policyData['limit']);
        } else {
            $policies = $this->repository->getAll($policyData['offset'], $policyData['limit']);
        }

        $rsp = [];
        foreach ($policies as $policy) {
            $tmp = new \Sentegrity\BusinessBundle\Transformers\Policy($policy);
            $rsp[] = $tmp;
            $tmp = null;
        }

        return $rsp;
    }
}