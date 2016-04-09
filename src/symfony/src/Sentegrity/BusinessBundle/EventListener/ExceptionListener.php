<?php
namespace Sentegrity\BusinessBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Sentegrity\BusinessBundle\Handlers\Response;
use Sentegrity\BusinessBundle\Transformers\Error;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExceptionListener
{
    /** @var ContainerInterface */
    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $e = $event->getException();
        Response::responseInternalServerError($this->unknownError($e));
        $event->setResponse(Response::$response);
    }

    private function unknownError(\Exception $e)
    {
        $error = new Error(
            $e->getCode(),
            $this->container->get('translator')->trans($e->getMessage()),
            $e->getFile() . " @line: " . $e->getLine() . " with message " . $e->getMessage()
        );

        return $error;
    }
}