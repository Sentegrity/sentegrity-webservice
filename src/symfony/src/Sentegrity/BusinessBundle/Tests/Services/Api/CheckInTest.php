<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Sentegrity\BusinessBundle\Tests\Services\Utility;
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
        Utility::init(static::createClient()->getContainer());
    }

    /**
     * @group api_check_in
     */
    public function testProcessExistingUser()
    {
        Utility::mockUser('test@domain23.dfg');

        // policyId exists new revision available
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 0], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain23.dfg',
            "device_salt"               => Utility::mockDeviceSalt(),
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v1.0'
        ]);

        $this->assertNotEmpty($rsp);
        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertEquals('value', $rsp->newPolicy->key);

        // policyId exists no new revision
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 0], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name',
            "current_policy_revision"   => 1,
            "user_activation_id"        => 'test@domain23.dfg',
            "device_salt"               => Utility::mockDeviceSalt(),
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v3.0'
        ]);

        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertNull($rsp->newPolicy);


        // policyId does not exist
        $rsp = self::$checkInService->processExistingUser([
            "organization_id"   => 1,
            "group_id"          => 1], [
            "platform"                  => Platform::IOS,
            "current_policy_id"         => 'Test policy name unexisting',
            "current_policy_revision"   => 0,
            "user_activation_id"        => 'test@domain23.dfg',
            "device_salt"               => Utility::mockDeviceSalt(),
            "phone_model"               => 'iPhone 5s',
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v3.0'
        ]);

        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertNull($rsp->newPolicy);
    }



    /**
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
            "run_history_objects"       => ['key' => 'value'],
            "app_version"               => 'v1.0'
        ]);

        $this->assertNotEmpty($rsp);
        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertEquals('value', $rsp->newPolicy->key);

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
        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertEquals('value', $rsp->newPolicy->key);

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

        $rsp = Utility::enablePrivateProperties($rsp);
        $this->assertNull($rsp->newPolicy);
        
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