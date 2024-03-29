<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Sentegrity\BusinessBundle\Annotations\Permission;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Admin\Organization;

/**
 * @Route("/admin/organization")
 */

class OrganizationController extends RootController
{
    /** @var Organization $organizationService */
    private $organizationService;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->organizationService = $this->container->get('sentegrity_business.organization');
    }
    
    /**
     * @Route(
     *      "/create",
     *      defaults={"_format" = "json"},
     *      name="admin_organization_create",
     *      methods="POST"
     * )
     * @Permission(
     *     permission = Permission::SUPERADMIN
     * )
     */
    public function createAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_organization_create')
        );

        return $this->response(
            $this->organizationService->create($requestData)
        );
    }

    /**
     * @Route(
     *      "/get/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_organization_get",
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
            $this->organizationService->read($requestData)
        );
    }

    /**
     * @Route(
     *      "/get/all",
     *      defaults={"_format" = "json"},
     *      name="admin_organization_get_all",
     *      methods="GET"
     * )
     * @Permission(
     *     permission = Permission::ADMIN
     * )
     */
    public function getAllAction(Request $request)
    {
        $requestData = $this->validate(
            $request,
            $this->container->getParameter('validate_load_get'),
            ValidateRequest::GET
        );

        return $this->response(
            $this->organizationService->getAllOrganizations($requestData)
        );
    }

    /**
     * @Route(
     *      "/edit/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_organization_edit",
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
            $this->container->getParameter('validate_organization_create')
        );

        $requestData['uuid'] = $uuid;

        return $this->response(
            $this->organizationService->update($requestData)
        );
    }

    /**
     * @Route(
     *      "/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_organization_delete",
     *      methods="DELETE"
     * )
     * @Permission(
     *     permission = Permission::SUPERADMIN
     * )
     */
    public function deleteAction($uuid)
    {
        $requestData = ['uuid' => $uuid];

        return $this->response(
            $this->organizationService->delete($requestData)
        );
    }
}