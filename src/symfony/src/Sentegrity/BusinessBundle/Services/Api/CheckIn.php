<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Services\Support\ErrorLog;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;

/**
 * This is more of a helper class which allows the code to be more
 * structured and clean. That will allow easier development and
 * coder revision. As well as inclusion of new developer on the project.
 */

class CheckIn extends Service
{
    /** @var User $user */
    private $user;
    /** @var Policy $policy */
    private $policy;
    /** @var Organization $organization */
    private $organization;
    /** @var ErrorLog $errorLog */
    private $errorLog;
    /** @var RunHistory $runHistory */
    private $runHistory;
    
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->user = $this->containerInterface->get('sentegrity_business.api.user');
        $this->policy = $this->containerInterface->get('sentegrity_business.api.policy');
        $this->organization = $this->containerInterface->get('sentegrity_business.api.organization');
        $this->errorLog = $this->containerInterface->get('sentegrity_business.error_log');
        $this->runHistory = $this->containerInterface->get('sentegrity_business.api.run_history');
    }

    /**
     * Process policy for existing user
     * 
     * @param array $groupAndOrganization
     * @param array $requestData
     * @return \stdClass
     */
    public function processExistingUser(array $groupAndOrganization, array $requestData)
    {
        $policyId = $this->policy->getPolicyIdByGroupOrganizationPlatform(
            $groupAndOrganization['group_id'],
            $groupAndOrganization['organization_id'],
            $requestData['platform']
        );

        if ($policyId) {
            $policy = $this->policy->getPolicyById($policyId);
            if (!$policy) {
                $this->errorLog->write(
                    "No policy match for group id " . $groupAndOrganization['group_id'] .
                    " and organization id " . $groupAndOrganization['organization_id'] . " id found",
                    ErrorLog::LOGIC_ERROR
                );
                $policyId = $this->policy->getPolicyIdByGroupOrganizationPlatform(
                    0,
                    $groupAndOrganization['organization_id'],
                    $requestData['platform']
                );
                $policy = $this->policy->getPolicyById($policyId);
            } else {
                $policy = $this->policy->getNewPolicyRevision(
                    $policyId,
                    $requestData['current_policy_revision'],
                    $requestData['platform']
                );
            }
        } else {
            $this->errorLog->write(
                "No policy match for group id " . $groupAndOrganization['group_id'] .
                " and organization id " . $groupAndOrganization['organization_id'] . " id found",
                ErrorLog::LOGIC_ERROR
            );
            $policy = null;
        }

        $this->runHistory->save([
            "user_activation_id"    => $requestData['user_activation_id'],
            "organization_id"       => $groupAndOrganization['organization_id'],
            "device_salt"           => $requestData['device_salt'],
            "phone_model"           => $requestData['phone_model'],
            "platform"              => $requestData['platform'],
            "objects"               => $requestData['run_history_objects']
        ]);

        return $policy;
    }

    /**
     * Process policy for existing user
     *
     * @param array $requestData
     * @return \stdClass
     * @throws ValidatorException
     */
    public function processNewUser(array $requestData)
    {
        $organization = $this->organization->getOrganizationByDomainName($requestData['user_activation_id']);
        if ($organization) {
            $this->user->create([
                "device_activation_id" => $requestData['user_activation_id'],
                "organization_id" => $organization,
                "group_id" => 0,
            ]);

            $policyId = $this->policy->getPolicyIdByGroupOrganizationPlatform(
                0,
                $organization,
                $requestData['platform']
            );

            $this->runHistory->save([
                "user_activation_id"    => $requestData['user_activation_id'],
                "organization_id"       => $organization,
                "device_salt"           => $requestData['device_salt'],
                "phone_model"           => $requestData['phone_model'],
                "platform"              => $requestData['platform'],
                "objects"               => $requestData['run_history_objects']
            ]);

            // always return default organization policy when a new user is created
            return $this->policy->getPolicyById($policyId);

        } else {
            if($policyId = $this->policy->checkIfDefault(
                $requestData['current_policy_id'],
                $requestData['platform'],
                0
            )) {
                $this->errorLog->write("Updated policy for user with no organization", ErrorLog::LOGIC_ERROR);
                return $this->policy->getNewPolicyRevision(
                    $policyId,
                    $requestData['current_policy_revision'],
                    $requestData['platform']
                );
            }
            throw new ValidatorException(
                null,
                'Update impossible',
                0
            );
        }
    }
}