<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Services\Admin\SignIn;

/**
 * @Route("/admin")
 */
class SignInController extends RootController
{
    /** @var SignIn $signInService */
    private $signInService;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->signInService = $this->container->get('sentegrity_business.sign_in');
    }

    /**
     * @Route(
     *      "/sign-in",
     *      defaults={"_format" = "json"},
     *      name="sign_in",
     *      methods="POST"
     * )
     */
    public function signInAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_sign_in'),
            ValidateRequest::POST
        );

        return $this->response(
            $this->signInService->signIn($requestData)
        );
    }

    /**
     * @Route(
     *      "/sign-out",
     *      defaults={"_format" = "json"},
     *      name="sign_out",
     *      methods="POST"
     * )
     */
    public function signOutAction(Request $request)
    {
        $requestData = $request->headers->get('access-token');

        return $this->response(
            $this->signInService->signOut($requestData)
        );
    }
}