<?php
namespace Sentegrity\BusinessBundle\EventListener;

use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
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

        switch ($e->getCode()) {
            case 200:
            case ErrorCodes::REQUEST_FILEDS_INVALID:
                Response::responseBadRequest($this->unknownError($e));
                break;
            case ErrorCodes::NOT_FOUND:
                Response::responseNotFound($this->unknownError($e));
                break;
            case ErrorCodes::FORBIDDEN:
                Response::responseForbidden($this->unknownError($e));
                break;
            case ErrorCodes::UNAUTHORIZED_ACCESS:
                Response::responseUnauthorised($this->unknownError($e));
                break;
            default:
                Response::responseInternalServerError($this->unknownError($e));
                break;
        }

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