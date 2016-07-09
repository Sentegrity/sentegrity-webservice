<?php
namespace Sentegrity\BusinessBundle\Tests\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Utility
{
    /** @var ContainerInterface $container */
    private static $container;

    public static function init(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function createPolicy($platform, $def = 0)
    {
        $policyService = self::$container
            ->get('sentegrity_business.policy');

        // just some test case data
        $policyData = array(
            "name" => 'Test policy name',
            "platform" => $platform,
            "is_default" => $def,
            "app_version" => 'v1.0',
            "data" => ['key' => 'value']
        );

        $rsp = $policyService->create($policyData);
        return $rsp->data;
    }
}