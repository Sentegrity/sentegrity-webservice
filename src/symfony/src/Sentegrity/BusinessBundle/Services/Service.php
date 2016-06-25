<?php
namespace Sentegrity\BusinessBundle\Services;

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

    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->translator = $this->containerInterface->get('translator');
        $this->mysqlq = $containerInterface->get('my_sql_query');
    }

    public function true()
    {
        $rsp = new \stdClass();
        $rsp->successfull = true;
        return $rsp;
    }

    public function false($msg = "")
    {
        $rsp = new \stdClass();
        $rsp->successfull = false;
        $rsp->msg = $msg;
        return $rsp;
    }
} 