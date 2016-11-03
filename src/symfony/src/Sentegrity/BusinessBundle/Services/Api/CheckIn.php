<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Services\Support\ErrorLog;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Transformers\CheckIn as Transformer;

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
    /** @var Transformer */
    private $transformer;
    
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->user = $this->containerInterface->get('sentegrity_business.api.user');
        $this->policy = $this->containerInterface->get('sentegrity_business.api.policy');
        $this->organization = $this->containerInterface->get('sentegrity_business.api.organization');
        $this->errorLog = $this->containerInterface->get('sentegrity_business.error_log');
        $this->runHistory = $this->containerInterface->get('sentegrity_business.api.run_history');

        $this->transformer = new Transformer();
    }

    /**
     * Process policy for existing user
     * 
     * @param array $groupAndOrganization
     * @param array $requestData
     * @return Transformer
     */
    public function processExistingUser(array $groupAndOrganization, array $requestData)
    {
        $this->identifyDevice(
            $requestData['device_salt'],
            $requestData['user_activation_id'],
            $groupAndOrganization
        );

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
                if ($policy) {
                    if ($this->checkPolicyAppVersion($policy, $requestData['app_version'])) {
                        $this->transformer->setPolicy($policy['data']);
                    }
                } else {
                    $this->transformer->setPolicyExists(false);
                }
            } else {
                if ($requestData['current_policy_id'] == $policy['name']) {
                    $policy = $this->policy->getNewPolicyRevision(
                        $policyId,
                        $requestData['current_policy_revision'],
                        $requestData['platform']
                    );
                    if ($this->checkPolicyAppVersion($policy, $requestData['app_version'])) {
                        $this->transformer->setPolicy($policy['data']);
                    }
                } else {
                    if ($this->checkPolicyAppVersion($policy, $requestData['app_version'])) {
                        $this->transformer->setPolicy($policy['data']);
                    }
                }
            }
        } else {
            $this->errorLog->write(
                "No policy match for group id " . $groupAndOrganization['group_id'] .
                " and organization id " . $groupAndOrganization['organization_id'] . " id found",
                ErrorLog::LOGIC_ERROR
            );
        }

        $this->runHistory->save([
            "user_activation_id"    => $requestData['user_activation_id'],
            "organization_id"       => $groupAndOrganization['organization_id'],
            "device_salt"           => $requestData['device_salt'],
            "phone_model"           => $requestData['phone_model'],
            "platform"              => $requestData['platform'],
            "objects"               => $requestData['run_history_objects']
        ]);

        return $this->transformer;
    }

    /**
     * Process policy for existing user
     *
     * @param array $requestData
     * @return Transformer
     */
    public function processNewUser(array $requestData)
    {
        $organization = $this->organization->getOrganizationByDomainName($requestData['user_activation_id']);
        if ($organization) {
            $this->user->create([
                "device_activation_id" => $requestData['user_activation_id'],
                "organization_id" => $organization,
                "group_id" => 0,
                "device_salt" => $requestData['device_salt']
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
            $policy =  $this->policy->getPolicyById($policyId);
            if ($policy) {
                if ($this->checkPolicyAppVersion($policy, $requestData['app_version'])) {
                    $this->transformer->setPolicy($policy['data']);
                }
            } else {
                $this->transformer->setPolicyExists(false);
            }

        } else {
            if($policyId = $this->policy->checkIfDefault(
                $requestData['current_policy_id'],
                $requestData['platform'],
                0
            )) {
                $this->errorLog->write("Updated policy for user with no organization", ErrorLog::LOGIC_ERROR);
                $policy = $this->policy->getNewPolicyRevision(
                    $policyId,
                    $requestData['current_policy_revision'],
                    $requestData['platform']
                );
                if ($this->checkPolicyAppVersion($policy, $requestData['app_version'])) {
                    $this->transformer->setPolicy($policy['data']);
                }
            }

            $this->transformer->setPolicyExists(false);
        }
        return $this->transformer;
    }

    /**
     * Checks if user has already registered with a current device. If not ir creates a new
     * record in database for a new device salt. Rest of data is copied
     *
     * @param $deviceSalt
     * @param $userActivationId
     * @param array $groupAndOrganization
     * @throws ValidatorException
     */
    private function identifyDevice($deviceSalt, $userActivationId, array $groupAndOrganization)
    {
        /***/
        $rsp = $this->mysqlq->select(
            'user',
            array('device_user_id'),
            array(
                'device_activation_id' => array('value' => $userActivationId)
            ), [], [], '',
            MySQLQuery::MULTI_ROWS
        );

        if (!$rsp) {
            throw new ValidatorException(
                null,
                'Internal server error has occurred. Please contact administrator to resolve this.',
                0
            );
        }

        foreach ($rsp as $record) {
            if ($deviceSalt == $record->device_user_id) {
                // this is an existing user with an existing device
                return;
            }
        }

        try {
            $this->user->create([
                "device_activation_id" => $userActivationId,
                "organization_id" => $groupAndOrganization['organization_id'],
                "group_id" => $groupAndOrganization['group_id'],
                "device_salt" => $deviceSalt
            ]);
        } catch (\Exception $e) {
            throw new ValidatorException(
                null,
                'Internal server error has occurred. Please contact administrator to resolve this.',
                0
            );
        }
    }

    /**
     * Check policy's app version
     * @return bool
     */
    private function checkPolicyAppVersion($policy, $appVersion)
    {
        return $policy['app_version'] == $appVersion;
    }
}