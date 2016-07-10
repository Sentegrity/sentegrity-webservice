<?php
namespace Sentegrity\BusinessBundle\Services;

use Sentegrity\BusinessBundle\Annotations\Permission;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\ErrorLog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class Service
{
    /** @var ContainerInterface $containerInterface */
    protected $containerInterface;

    /** @Translator $translator */
    protected $translator;

    /** @var MySQLQuery $mysqlq */
    protected $mysqlq;

    /** @var \Doctrine\ORM\EntityManager $entityManager */
    protected $entityManager;

    /** @var SessionInterface */
    protected $session;

    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->translator = $this->containerInterface->get('translator');
        $this->mysqlq = $containerInterface->get('my_sql_query');
        $this->entityManager = $containerInterface->get('doctrine')->getManager();
        $this->session = $containerInterface->get('session');
    }

    public function true($data = "")
    {
        $rsp = new \stdClass();
        $rsp->successful = true;
        $rsp->data = $data;
        return $rsp;
    }

    public function false($msg = "")
    {
        $rsp = new \stdClass();
        $rsp->successful = false;
        $rsp->msg = $msg;
        return $rsp;
    }

    /**
     * Smart flush
     * @param $errorMessage
     * @return \stdClass
     */
    protected function flush(
        $errorMessage = "An error occurred while performing this action",
        $successData = ""
    ) {
        try {
            $this->entityManager->flush();
            $this->entityManager->clear();
        } catch (\Exception $e) {
            /** @var ErrorLog $errorLog */
            $errorLog = $this->containerInterface->get('sentegrity_business.error_log');
            $errorLog->write($e->getMessage(), ErrorLog::PHP_ERROR);

            return $this->false(
                $this->translator->trans($errorMessage)
            );
        }

        return $this->true($successData);
    }
    
    /**
     * Validates request against data in session
     * 
     * @param $organization
     * @throws ValidatorException
     */
    protected function checkSession($organization)
    {
        if ($this->checkIfSuperAdmin()) {
            return;
        }
        
        $this->checkIfOrganizationValid($organization);
    }
    
    /**
     * Check if current user is super admin
     *
     * @param $only -> this flag marks that only a super admin check will be performed
     * @return bool
     * @throws ValidatorException
     */
    protected function checkIfSuperAdmin($only = false)
    {
        $yes = $this->session->get('permission') == Permission::SUPERADMIN;

        if ($only) {
            if (!$yes) {
                throw new ValidatorException(
                    null,
                    "You have no permissions for this action",
                    ErrorCodes::FORBIDDEN
                );
            }
        }
        return $yes;
    }

    /**
     * Check if current user is part of an organization
     * 
     * @param $organization
     * @throws ValidatorException
     */
    protected function checkIfOrganizationValid($organization)
    {
        if ($this->session->get('org_uuid') == $organization ||
            $this->session->get('org_id') == $organization &&
            is_int($organization)
        ) {
            return;
        }

        throw new ValidatorException(
            null,
            "You have no permissions for this action",
            ErrorCodes::FORBIDDEN
        );
    }
} 