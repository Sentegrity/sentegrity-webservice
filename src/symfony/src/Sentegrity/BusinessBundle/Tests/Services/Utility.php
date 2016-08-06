<?php
namespace Sentegrity\BusinessBundle\Tests\Services;

use Sentegrity\BusinessBundle\Services\Admin\SignIn;
use Sentegrity\BusinessBundle\Services\Support\UUID;
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

    /**
     * Creates fake policy
     *
     * @param $platform
     * @param $def -> 1 - default, 0 - not default
     * @return \stdClass
     */
    public static function mockPolicy($platform, $def = 0)
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

    /**
     * Creates fake user session. User of testing admin api calls
     */
    public static function mockUserSession()
    {
        /** @var SessionInterface */
        $session = self::$container->get('session');
        $session->set('org_uuid', "");
        $session->set('permission', 0);
    }

    /**
     * Creates fake device salt
     */
    public static function mockDeviceSalt()
    {
        // just generate some random UUID, it will serve good enough
        // to mock the device salt for test cases
        return UUID::generateUuid();
    }

    /**
     * Creates fake user in database.
     *
     * @param $email
     */
    public static function mockUser($email = "test@domain.dfg")
    {
        /** @var \Sentegrity\BusinessBundle\Services\Api\User $userService */
        $userService = self::$container
            ->get('sentegrity_business.api.user');
        $userService->create(array(
            "device_activation_id" => $email,
            "organization_id" => 1,
            "group_id" => 0,
            "device_salt" => self::mockDeviceSalt()
        ));
    }
}