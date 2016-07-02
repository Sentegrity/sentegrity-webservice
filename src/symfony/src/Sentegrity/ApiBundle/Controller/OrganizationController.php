<?php
namespace Sentegrity\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Services\Organization;

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
     *      "/edit/{uuid}",
     *      defaults={"_format" = "json"},
     *      requirements={"uuid" = UUID::UUID_REGEX},
     *      name="admin_organization_edit",
     *      methods="POST"
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
     */
    public function deleteAction($uuid)
    {
        $requestData = ['uuid' => $uuid];

        return $this->response(
            $this->organizationService->delete($requestData)
        );
    }
}