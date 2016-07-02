<?php
namespace Sentegrity\BusinessBundle\Services;

use Sentegrity\BusinessBundle\Entity\Documents\Groups;
use Sentegrity\BusinessBundle\Entity\Repository\OrganizationRepository;
use Sentegrity\BusinessBundle\Entity\Repository\PolicyRepository;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Entity\Documents\Organization as OrganizationEntity;

class Organization extends Service
{
    /** @var OrganizationRepository $repository */
    private $repository;
    
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->repository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\Organization'
        );
    }

    /**
     * Creates a new organization. When new organization is created a new group for that
     * organization is created with a group id of 0.
     *
     * @param array $organizationData
     * @return \stdClass
     */
    public function create(array $organizationData)
    {
        /**
         * $organizationData template:
         * array(
         *      "name" => ...,
         *      "domain_name" => ...,
         *      "contact_name" => ...,
         *      "contact_email" => ...,
         *      "contact_phone" => ...,
         *      "policy_ios" => uuid of a policy,
         *      "policy_android" => uuid of a policy,
         * )
         */

        // create fresh uuid for policy, use current timestamp as a seed
        $uuid = UUID::generateUuid(time());

        // now lets create an organization from given data
        $organization = new OrganizationEntity();
        $organization->setUuid($uuid)
            ->setName($organizationData['name'])
            ->setDomainName($organizationData['domain_name'])
            ->setContactName($organizationData['contact_name'])
            ->setContactEmail($organizationData['contact_email'])
            ->setContactPhone($organizationData['contact_phone']);

        $this->entityManager->persist($organization);

        // now we need to create a default group for this organization
        // but before that we need to get policies by their uuids
        /** @var Policy $policyService */
        $policyService = $this->containerInterface->get('sentegrity_business.policy');
        $iosPolicy = $policyService->getPolicyByUuid($organizationData['policy_ios']);
        $androidPolicy = $policyService->getPolicyByUuid($organizationData['policy_android']);

        // so now than we have everything let's create a group
        $group = new Groups(0, $organization);
        $group->setPolicyIos($iosPolicy)
            ->setPolicyIdAndroid($androidPolicy);
        $this->entityManager->persist($group);

        return $this->flush(
            'An error occurred while creating organization. Create failed!',
            $uuid
        );
    }

    /** 
     * Updates an organization. Basically, everything there is can be updated.
     * 
     * @param array $organizationData
     * @return \stdClass
     */
    public function update(array $organizationData)
    {
        /**
         * In addition to organizationData array from create, this one contains uuid field
         * to enables us to find current organization and update it.
         */

        $organization = $this->getOrganizationByUuid($organizationData['uuid']);
        $organization->setName($organizationData['name'])
            ->setDomainName($organizationData['domain_name'])
            ->setContactName($organizationData['contact_name'])
            ->setContactEmail($organizationData['contact_email'])
            ->setContactPhone($organizationData['contact_phone']);

        // if policy is changed we need to update group as well
        /** @var Policy $policyService */
        $policyService = $this->containerInterface->get('sentegrity_business.policy');
        $iosPolicy = $policyService->getPolicyByUuid($organizationData['policy_ios']);
        $androidPolicy = $policyService->getPolicyByUuid($organizationData['policy_android']);
        
        // get group
        /** @var Group $groupService */
        $groupService = $this->containerInterface->get('sentegrity_business.group');
        $group = $groupService->getGroupByGroupAndOrganization(0, $organization);
        $group->setPolicyIos($iosPolicy)
            ->setPolicyIdAndroid($androidPolicy);

        return $this->flush(
            'An error occurred while updating organization. Edit failed!',
            $organizationData['uuid']
        );
    }

    /**
     * Gets policy from database. This a simple read that performs read only by uuid. More
     * complex read will be used to get more specific policy. This here is mostly for admin
     * usage.
     *
     * @param array $policyData
     * @return \Sentegrity\BusinessBundle\Transformers\Organization
     */
    public function read(array $organizationData)
    {
        /**
         * This organization data contains only uuid. It is left as an array in case some other
         * data needs to be added, and also to keep consistency.
         */

        /** @var Group $groupService */
        $groupService = $this->containerInterface->get('sentegrity_business.group');
        $organizationEntity = $this->getOrganizationByUuid($organizationData['uuid']);

        $organization = new \Sentegrity\BusinessBundle\Transformers\Organization(
            $organizationEntity,
            $groupService->getGroupByGroupAndOrganization(0, $organizationEntity)
        );

        return $organization;
    }

    /**
     * Deletes organization record from database by given uuid.
     *
     * @param array $organizationData
     * @return \stdClass
     */
    public function delete(array $organizationData)
    {
        /***/
        $organization = $this->getOrganizationByUuid($organizationData['uuid']);

        $id = $organization->getId();
        /** @var PolicyRepository $policyRepository */
        $policyRepository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\Policy'
        );
        $policyRepository->deleteByOrganization($id);

        $this->entityManager->remove($organization);
        return $this->flush(
            'An error occurred while deleting policy. Delete failed!'
        );
    }

    /**
     * Gets organization from database by given uuid
     *
     * @param $uuid
     * @return OrganizationEntity
     * @throws ValidatorException
     */
    public function getOrganizationByUuid($uuid)
    {
        $organization = $this->repository->getByUuid($uuid);

        if (!$organization) {
            throw new ValidatorException(
                null,
                $this->translator->trans('Organization with a given uuid not founded.'),
                ErrorCodes::NOT_FOUND
            );
        }

        return $organization;
    }

    /**
     * Gets id from database by given uuid
     *
     * @param $uuid
     * @return int $id
     * @throws ValidatorException
     */
    public function getOrganizationIdByUuid($uuid)
    {
        $id = $this->repository->getIdByUuid($uuid);

        if(!$id) {
            throw new ValidatorException(
                null,
                $this->translator->trans('Organization with a given uuid not founded.'),
                ErrorCodes::NOT_FOUND
            );
        }

        return $id;
    }
}