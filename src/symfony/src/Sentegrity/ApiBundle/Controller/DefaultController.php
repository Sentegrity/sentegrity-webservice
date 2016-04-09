<?php

namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Handlers as Handler;

class DefaultController extends RootController
{
    /**
     * @Route(
     *      "/",
     *      defaults={"_format" = "json"},
     *      name="default_post",
     *      methods="GET"
     * )
     */
    public function indexAction()
    {
        $rsp = new \stdClass();
        $rsp->version = $this->container->getParameter('api_version');
        $rsp->type = "dev";

        Handler\Response::responseOK($rsp);
        return Handler\Response::$response;
    }
}
