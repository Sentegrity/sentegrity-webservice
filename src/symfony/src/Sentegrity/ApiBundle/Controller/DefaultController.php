<?php

namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Annotations\Permission;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Admin\Policy;
use Sentegrity\BusinessBundle\Services\Admin\Organization;

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

    /**
     * @Route(
     *      "/admin/pagination-data",
     *      defaults={"_format" = "json"},
     *      name="admin_pagination_data",
     *      methods="GET"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function adminPaginationDataAction()
    {
        /** @var Policy $policyService */
        $policyService = $this->container->get('sentegrity_business.policy');
        /** @var Organization $organizationService */
        $organizationService = $this->container->get('sentegrity_business.organization');
        
        return $this->response([
            "policyCount"       => $policyService->countPoliciesByOrganization(),
            "organizationCount" => $organizationService->countOrganizations()
        ]);
    }
}
