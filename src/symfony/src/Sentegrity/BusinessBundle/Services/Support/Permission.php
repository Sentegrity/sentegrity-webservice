<?php
namespace Sentegrity\BusinessBundle\Services\Support;

use Sentegrity\BusinessBundle\Entity\Repository\AdminSessionRepository;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Annotations\Permission as AnnotationConstants;
use Symfony\Component\HttpFoundation\Session\Session;

class Permission
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManager $em
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
    }
    
    /**
     * Check permission, if okay returns true, otherwise throws an error
     * 
     * @param $permission
     * @return bool
     * @throws ValidatorException
     */
    public function check($permission)
    {
        $accessToken = $this->request->headers->get('access-token');
        $currentPermission = $this->getPermissionFromAccessToken($accessToken);
        if ($currentPermission <= $permission) {
            return true;
        }

        throw new ValidatorException(
            null,
            "You have no permissions for this action",
            ErrorCodes::FORBIDDEN
        );
    }
    
    /**
     * Search database's session table to meet permission associated to
     * this access token. Throws an exception if no permission is found.
     * 
     * @param $accessToken
     * @return permission
     * @throws ValidatorException
     */
    private function getPermissionFromAccessToken($accessToken)
    {
        /** @var AdminSessionRepository $sessionRepository */
        $sessionRepository = $this->em->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\AdminSession'
        );

        $adminSession = $sessionRepository->getSessionByAccessToken($accessToken);
        $permission = $adminSession->getPermission();
        $session = $this->request->getSession();
        $currOrg = $adminSession->getOrganization();
        if ($currOrg) {
            $orgUuid = $currOrg->getUuid();
            $orgId = $currOrg->getId();
        } else {
            $orgUuid = "";
            $orgId = 0;
        }
        $session->set('org_uuid', $orgUuid);
        $session->set('org_id', $orgId);
        $session->set('permission', $permission);

        return $permission;
    }
}