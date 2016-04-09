<?php
namespace Sentegrity\BusinessBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Service
{
    /** @var ContainerInterface $containerInterface */
    protected $containerInterface;

    /** @Translator $translator */
    protected $translator;

    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->translator = $this->containerInterface->get('translator');
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