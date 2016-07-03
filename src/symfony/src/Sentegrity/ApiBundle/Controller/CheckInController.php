<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Api\Organization;
use Sentegrity\BusinessBundle\Services\Api\Policy;
use Sentegrity\BusinessBundle\Services\Api\User;
use Sentegrity\BusinessBundle\Services\Support\ErrorLog;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Route("/api")
 */

class CheckInController extends RootController
{
    /** @var User $user */
    private $user;
    /** @var Policy $policy */
    private $policy;
    /** @var Organization $organization */
    private $organization;
    /** @var ErrorLog $errorLog */
    private $errorLog;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->user = $this->container->get('sentegrity_business.api.user');
        $this->policy = $this->container->get('sentegrity_business.api.policy');
        $this->organization = $this->container->get('sentegrity_business.api.organization');
        $this->errorLog = $this->container->get('sentegrity_business.error_log');
    }
    
    /**
     * @Route(
     *      "/check-in",
     *      defaults={"_format" = "json"},
     *      name="check_in",
     *      methods="POST"
     * )
     */
    public function checkInAction(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        
        $groupAndOrganization = $this->user->getGroupAndOrganization($requestData['user_activation_id']);
        if ($groupAndOrganization) {
            
        } else {
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

                return $this->response(
                    $this->policy->getNewPolicyRevision(
                        $policyId,
                        $requestData['current_policy_revision'],
                        $requestData['platform']
                    )
                );
                
            } else {
                if($policyId = $this->policy->checkIfDefault(
                    $requestData['current_policy_id'],
                    $requestData['platform'], 
                    0
                )) {
                    $this->errorLog->write("Updated policy for user with no organization", ErrorLog::LOGIC_ERROR);
                    return $this->response(
                        $this->policy->getNewPolicyRevision(
                            $policyId,
                            $requestData['current_policy_revision'],
                            $requestData['platform']
                        )
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
}