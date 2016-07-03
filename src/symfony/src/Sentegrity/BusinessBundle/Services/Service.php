<?php
namespace Sentegrity\BusinessBundle\Services;

use Sentegrity\BusinessBundle\Services\Support\ErrorLog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;

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

    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->translator = $this->containerInterface->get('translator');
        $this->mysqlq = $containerInterface->get('my_sql_query');
        $this->entityManager = $containerInterface->get('doctrine')->getManager();
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
} 