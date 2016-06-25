<?php
namespace Sentegrity\BusinessBundle\Handlers;


use Symfony\Component\DependencyInjection\ContainerInterface;

class MySQL
{
    /**
     * @var \PDO
     */
    private static $masterConnection;

    /**
     * @var \PDO
     */
    private static $slaveConnection;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $containerInterface
     */
    public static function setConnection(ContainerInterface $containerInterface)
    {

        self::$masterConnection = $containerInterface->get('pdo_master_connection');
        self::$slaveConnection = $containerInterface->get('pdo_slave_connection');
    }

    /**
     * @return \PDO
     */
    public static function getMasterConnection()
    {
        return self::$masterConnection;
    }

    /**
     * @return \PDO
     */
    public static function getSlaveConnection()
    {
        return self::$slaveConnection;
    }
} 