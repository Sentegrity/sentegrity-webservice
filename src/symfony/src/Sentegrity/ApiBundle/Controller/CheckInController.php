<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Api\CheckIn;
use Sentegrity\BusinessBundle\Services\Api\User;
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
    /** @var CheckIn $processor */
    private $processor;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->user = $this->container->get('sentegrity_business.api.user');
        $this->processor = $this->container->get('sentegrity_business.api.check_in');
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
        
        $groupAndOrganization = $this->user->getGroupAndOrganization(
            $requestData['user_activation_id']
        );
        if ($groupAndOrganization) {
            return $this->response(
                $this->processor->processExistingUser(
                    $groupAndOrganization,
                    $requestData
                )
            );

        } else {
            return $this->response(
                $this->processor->processNewUser(
                    $requestData
                )
            );
        }
    }
}