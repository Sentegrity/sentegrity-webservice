<?php
namespace Sentegrity\BusinessBundle\Tests\Services;

use Sentegrity\BusinessBundle\Services\Admin\SignIn;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Utility
{
    /** @var ContainerInterface $container */
    private static $container;
    public static $accessToken;

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

    public static function setUserSession()
    {
        /** @var SessionInterface */
        $session = self::$container->get('session');
        $session->set('org_uuid', "");
        $session->set('permission', 0);
    }
}