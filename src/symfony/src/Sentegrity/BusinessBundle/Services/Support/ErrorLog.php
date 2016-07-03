<?php
namespace Sentegrity\BusinessBundle\Services\Support;

use Sentegrity\BusinessBundle\Entity\Documents\ErrorLog as ErrorLogEntity;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Entity\Repository\ErrorLogRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ErrorLog extends Service
{
    const PHP_ERROR = 1;
    const LOGIC_ERROR = 2;

    /** @var ErrorLogRepository $repository */
    private $repository;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->repository = $this->entityManager->getRepository(
            '\Sentegrity\BusinessBundle\Entity\Documents\ErrorLog'
        );
    }

    /**
     * Writes error in database
     * @param $text -> error
     * @param $type
     */
    public function write($text, $type)
    {
        /***/
        $log = new ErrorLogEntity();
        $log->setText($text)
            ->setType($type)
            ->setCreated(time());

        $this->entityManager->persist($log);

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // TODO: something
        }
    }
}