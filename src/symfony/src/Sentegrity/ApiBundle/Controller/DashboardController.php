<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Services\Admin\Dashboard;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Sentegrity\BusinessBundle\Annotations\Permission;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Handlers as Handler;

/**
 * @Route("/admin/dashboard")
 */
class DashboardController extends RootController
{
    /** @var Dashboard $dashboardService */
    private $dashboardService;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->dashboardService = $this->container->get('sentegrity_business.dashboard');
    }

    /**
     * @Route(
     *      "/graph",
     *      defaults={"_format" = "json"},
     *      name="admin_dashboard_get_graph",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function getGraphAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_dashboard_get')
        );

        return $this->response(
            $this->dashboardService->getGraphData($requestData)
        );
    }

    /**
     * @Route(
     *      "/top",
     *      defaults={"_format" = "json"},
     *      name="admin_dashboard_get_top",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function getTopAction(Request $request)
    {
        $this->validate(
            $request,
            $this->container->getParameter('validate_dashboard_get')
        );

        $requestData = json_decode($request->getContent(), true);

        return $this->response(
            $this->dashboardService->getTopData($requestData)
        );
    }
}