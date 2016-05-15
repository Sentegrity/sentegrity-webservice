<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\ValidateRequest;
use Symfony\Component\HttpFoundation\Request;

class CheckInController extends RootController
{
    /**
     * @Route(
     *      "/checkin",
     *      defaults={"_format" = "json"},
     *      name="checkin",
     *      methods="POST"
     * )
     */
    public function checkInAction(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        ValidateRequest::validateRequestBody($requestData);

        /** @var \Sentegrity\BusinessBundle\Services\RunHistory $runHistory */
        $runHistory = $this->container->get('sentegrity_business.run_history');
        $runHistory->saveRunHistoryObjects(
            $requestData['email'],
            $requestData['deviceSalt'],
            $requestData['runHistoryObjects']
        );

        /** @var \Sentegrity\BusinessBundle\Services\Policy $policy */
        $policy = $this->container->get('sentegrity_business.policy');
        $newPolicy = $policy->checkPolicy(
            $requestData['policyID'],
            $requestData['policyRevision'],
            $requestData['email']
        );

        $rsp = new \stdClass();
        $rsp->newPolicy = $newPolicy;

        Handler\Response::responseOK($rsp);
        return Handler\Response::$response;
    }
}