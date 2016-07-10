<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Sentegrity\BusinessBundle\Annotations\Permission;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Admin\Policy;

/**
 * @Route("/admin/policy")
 */

class PolicyController extends RootController
{
    /** @var Policy $policyService */
    private $policyService;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->policyService = $this->container->get('sentegrity_business.policy');
    }

    /**
     * @Route(
     *      "/create",
     *      defaults={"_format" = "json"},
     *      name="admin_policy_create",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::WRITE
     * )
     */
    public function createAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_policy_create')
        );

        return $this->response(
            $this->policyService->create($requestData)
        );
    }

    /**
     * @Route(
     *      "/get/organization",
     *      defaults={"_format" = "json"},
     *      name="admin_policy_get_by_organization",
     *      methods="GET"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function getByOrganizationAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_load_get'),
            ValidateRequest::GET
        );

        return $this->response(
            $this->policyService->getPolicesByOrganization($requestData)
        );
    }

    /**
     * @Route(
     *      "/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_policy_get",
     *      methods="GET"
     * )
     * @Permission(
     *     permission = Permission::READ
     * )
     */
    public function getAction($uuid)
    {
        $requestData = ['uuid' => $uuid];

        return $this->response(
            $this->policyService->read($requestData)
        );
    }

    /**
     * @Route(
     *      "/edit/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_policy_edit",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::WRITE
     * )
     */
    public function editAction($uuid, Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_policy_create')
        );

        $requestData['uuid'] = $uuid;

        return $this->response(
            $this->policyService->update($requestData)
        );
    }

    /**
     * @Route(
     *      "/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_policy_delete",
     *      methods="DELETE"
     * )
     * @Permission(
     *     permission = Permission::WRITE
     * )
     */
    public function deleteAction($uuid)
    {
        $requestData = ['uuid' => $uuid];

        return $this->response(
            $this->policyService->delete($requestData)
        );
    }
}