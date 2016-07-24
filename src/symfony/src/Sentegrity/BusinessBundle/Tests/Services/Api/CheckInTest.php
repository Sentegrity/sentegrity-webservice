<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckInTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Api\CheckIn $checkInService */
    public static $checkInService;

    public static function setUpBeforeClass()
    {
        self::$checkInService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.check_in');

        /** @var \Sentegrity\BusinessBundle\Services\Api\User $userService */
        $userService = $checkInService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.user');
        $userService->create(array(
            "device_activation_id" => "test@domain4.com",
            "organization_id" => 1,
            "group_id" => 0
        ));
    }

    /**
     * @group api_check_in
     */
    public function testProcessExistingUser()
    {
        // policyId exists new revision available
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 0], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain.dfg',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value']
        ]);

        $this->assertNotEmpty($rsp);
        $this->assertEquals('value', $rsp->key);

        // policyId exists no new revision
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 0], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 1,
            "user_activation_id"        => 'test@domain.dfg',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value']
        ]);

        $this->assertNull($rsp);


        // policyId does not exist
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 1], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain.dfg',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value']
        ]);

        $this->assertNull($rsp);
    }

    /**
     * @expectedException        \Sentegrity\BusinessBundle\Exceptions\ValidatorException
     * @expectedExceptionMessage Update impossible
     * @group api_check_in
     */
    public function testProcessNewUser()
    {
        // existing organization
        $rsp = self::$checkInService->processNewUser([
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain.test',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value']
        ]);

        $this->assertNotEmpty($rsp);
        $this->assertEquals('value', $rsp->key);

        // no organization but default policy with old revision
        self::$checkInService->processNewUser([
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain27.test',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v3.0'
        ]);

        $this->assertNotEmpty($rsp);
        $this->assertEquals('value', $rsp->key);

        // no organization but default policy with valid revision
        $rsp = self::$checkInService->processNewUser([
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 1,
            "user_activation_id"        => 'test@domain28.test',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v3.0'
        ]);

        $this->assertNull($rsp);
        
        // no organization and policy is not default
        self::$checkInService->processNewUser([
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain27.test',
            "device_salt"               => 'qwerty',
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v2.0'
        ]);
    }
}