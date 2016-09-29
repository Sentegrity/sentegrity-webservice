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
     *      "/",
     *      defaults={"_format" = "json"},
     *      name="admin_dashboard_get",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function getAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_dashboard_get')
        );

        return $this->response(
            $this->dashboardService->getData($requestData)
        );
    }
}