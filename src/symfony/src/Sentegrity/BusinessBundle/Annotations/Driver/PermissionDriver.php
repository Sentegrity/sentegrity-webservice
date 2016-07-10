<?php
namespace Sentegrity\BusinessBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;//This thing read annotations
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\Permission;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;//Use essential kernel component
use Sentegrity\BusinessBundle\Annotations;//Use our annotation

class PermissionDriver
{
    private $reader;
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    public function __construct(
        $reader,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManager $em
    ) {
        $this->reader = $reader; //get annotations reader
        $this->requestStack = $requestStack;
        $this->em = $em;
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
            if (isset($configuration->permission)) {//Found our annotation
                $permission = new Permission($this->requestStack, $this->em);
                $permission->check($configuration->permission);
            }
        }
    }
}