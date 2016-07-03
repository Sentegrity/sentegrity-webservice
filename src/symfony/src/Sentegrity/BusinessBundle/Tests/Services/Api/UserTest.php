<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Api\User $userService */
    public static $userService;

    public static function setUpBeforeClass()
    {
        self::$userService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.user');
    }

    /**
     * @group api_user
     */
    public function testCreate()
    {
         $userData = array(
              "device_activation_id" => "test@domain.com",
              "organization_id" => 1,
              "group_id" => 0
         );
        
        $rsp = self::$userService->create($userData);
        $this->assertTrue($rsp, "User creation failed");
    }

    /**
     * @group api_user
     */
    public function testGetGroupAndOrganization()
    {
        // first create a new user
        $userData = array(
            "device_activation_id" => "test@domain2.com",
            "organization_id" => 1,
            "group_id" => 0
        );

        self::$userService->create($userData);
        $rsp = self::$userService->getGroupAndOrganization($userData['device_activation_id']);
        $this->assertEquals($rsp['group_id'], $userData['group_id'], "Group is not matching");
        $this->assertEquals($rsp['organization_id'], $userData['organization_id'], "Organization is not matching");
    }
}