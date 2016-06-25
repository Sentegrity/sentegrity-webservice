<?php

namespace Sentegrity\BusinessBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;//This thing read annotations
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;//Use essential kernel component
use Sentegrity\BusinessBundle\Annotations;//Use our annotation
use Sentegrity\BusinessBundle\Services\Support\Validator;


class ValidatorDriver
{
    private $reader;
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    public function __construct(
        $reader,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack
    ) {
        $this->reader = $reader; //get annotations reader
        $this->requestStack = $requestStack;
    }

    /**
     * This event will fire during any controller call
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }
        $object = new \ReflectionObject($controller[0]);// get controller
        $method = $object->getMethod($controller[1]);// get method

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) { //Start of annotations reading
            if (isset($configuration->key)) {//Found our annotation
                $validator = new Validator($this->requestStack);
                if ($rsp = $validator->queryParameter($configuration->key)) {
                    throw new ValidatorException($rsp, "Invalid fields", 1200);
                }
            }
        }
    }
}