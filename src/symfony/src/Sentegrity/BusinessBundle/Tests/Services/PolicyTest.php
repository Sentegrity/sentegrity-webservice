<?php
namespace Sentegrity\BusinessBundle\Tests\Services;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PolicyTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Policy $policyService */
    public static $policyService;
    
    public static function setUpBeforeClass()
    {
        self::$policyService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.policy');
    }
    
    /**
     * @expectedException        \Sentegrity\BusinessBundle\Exceptions\ValidatorException
     * @expectedExceptionMessage Policy with a given uuid not founded.
     * @group policy
     */
    public function testCRUD()
    {
        // just some test case data
        $policyData = array(
            "name" => 'Test policy name',
            "platform" => Platform::IOS,
            "is_default" => 1,
            "app_version" => 'v1.0',
            "data" => ['key' => 'value']
        );
        
        $rsp = self::$policyService->create($policyData);
        $this->assertTrue($rsp->successful, 'New policy not saved');

        // while performing this we can also test read
        $uuid = $rsp->data;
        // this way we'll make sure all is stored as it should be
        $rsp = self::$policyService->read(['uuid' => $uuid]);

        // here we can also test if json coders are working
        $rsp = json_encode($rsp);
        $rsp = json_decode($rsp);
        $this->assertEquals($rsp->name, $policyData['name'],                'Name is not good');
        $this->assertEquals($rsp->platform, $policyData['platform'],        'Platform is not good');
        $this->assertEquals($rsp->isDefault, $policyData['is_default'],     'Default falg is not good');
        $this->assertEquals($rsp->appVersion, $policyData['app_version'],   'App version is not good');
        $this->assertEquals($rsp->organization, 0,                          'Organization is not good');
        $this->assertEquals($rsp->data->key, $policyData['data']['key'],    'Data is not good');

        // after create and read are good lt's check update
        $policyData = array(
            "uuid" => $uuid,
            "name" => 'Test policy name edit',
            "platform" => Platform::ANDROID, // this should not be edited
            "is_default" => 0,
            "app_version" => 'v1.1',
            "data" => ['key' => 'value edit']
        );

        $rsp = self::$policyService->update($policyData);
        $rsp = json_encode($rsp);
        $rsp = json_decode($rsp);
        $this->assertEquals($rsp->name, $policyData['name'],                'Name is not good');
        $this->assertEquals($rsp->platform, Platform::IOS,                  'Platform is not good');
        $this->assertEquals($rsp->isDefault, $policyData['is_default'],     'Default falg is not good');
        $this->assertEquals($rsp->appVersion, $policyData['app_version'],   'App version is not good');
        $this->assertEquals($rsp->data->key, $policyData['data']['key'],    'Data is not good');

        // and at the end, test delete
        $rsp = self::$policyService->delete(['uuid' => $uuid]);
        $this->assertTrue($rsp->successful, 'New policy not deleted');

        // should throw an exception
        self::$policyService->read(['uuid' => $uuid]);
    }
}