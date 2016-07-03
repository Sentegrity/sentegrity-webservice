<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Admin;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrganizationTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Admin\Organization $organizationService */
    public static $organizationService;

    public static $iosUuid;
    public static $androidUuid;

    public static function setUpBeforeClass()
    {
        self::$organizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization');

        // first we need to create two new policies, both for iOS and Android
        self::$iosUuid = self::createPolicy(Platform::IOS);
        self::$androidUuid = self::createPolicy(Platform::ANDROID);
    }

    /**
     * @expectedException        \Sentegrity\BusinessBundle\Exceptions\ValidatorException
     * @expectedExceptionMessage Organization with a given uuid not founded.
     * @group admin_organization
     */
    public function testCRUD()
    {
        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domain.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => self::$iosUuid,
            "policy_android" => self::$androidUuid
        );

        $rsp = self::$organizationService->create($organizationData);
        $this->assertTrue($rsp->successful, 'New organization not saved');

        // while performing this we can also test read
        $uuid = $rsp->data;
        // this way we'll make sure all is stored as it should be
        $rsp = self::$organizationService->read(['uuid' => $uuid]);

        // here we can also test if json coders are working
        $rsp = json_encode($rsp);
        $rsp = json_decode($rsp);
        $this->assertEquals($rsp->name, $organizationData['name'],                      'Name is not good');
        $this->assertEquals($rsp->domainName, $organizationData['domain_name'],         'Domain name is not good');
        $this->assertEquals($rsp->contact->name, $organizationData['contact_name'],     'Contact name is not good');
        $this->assertEquals($rsp->contact->email, $organizationData['contact_email'],   'Contact email is not good');
        $this->assertEquals($rsp->contact->phone, $organizationData['contact_phone'],   'Contact phone is not good');
        $this->assertEquals($rsp->defaultPolicies->ios, self::$iosUuid,                 'iOS policy is not good');
        $this->assertEquals($rsp->defaultPolicies->android, self::$androidUuid,         'Android policy is not good');

        // after create and read are good lt's check update
        $iosUuid = $this->createPolicy(Platform::IOS, 0);
        $organizationData = array(
            "uuid" => $uuid,
            "name" => 'Test organization name edit',
            "domain_name" => 'domain-edit.test',
            "contact_name" => 'Contact Name Edit',
            "contact_email" => 'contact.email.edit@domain.test',
            "contact_phone" => '+1 234 5670',
            "policy_ios" => self::$iosUuid,
            "policy_android" => self::$androidUuid
        );

        $rsp = self::$organizationService->update($organizationData);
        $this->assertTrue($rsp->successful, 'Organization not updated');
        $rsp = self::$organizationService->read(['uuid' => $rsp->data]);

        // here we can also test if json coders are working
        $rsp = json_encode($rsp);
        $rsp = json_decode($rsp);
        $this->assertEquals($rsp->name, $organizationData['name'],                      'Name is not good');
        $this->assertEquals($rsp->domainName, $organizationData['domain_name'],         'Domain name is not good');
        $this->assertEquals($rsp->contact->name, $organizationData['contact_name'],     'Contact name is not good');
        $this->assertEquals($rsp->contact->email, $organizationData['contact_email'],   'Contact email is not good');
        $this->assertEquals($rsp->contact->phone, $organizationData['contact_phone'],   'Contact phone is not good');
        $this->assertEquals($rsp->defaultPolicies->ios, self::$iosUuid,                 'iOS policy is not good');
        $this->assertEquals($rsp->defaultPolicies->android, self::$androidUuid,         'Android policy is not good');

        // and at the end, test delete
        $rsp = self::$organizationService->delete(['uuid' => $uuid]);
        $this->assertTrue($rsp->successful, 'New organization not deleted');

        // should throw an exception
        self::$organizationService->read(['uuid' => $uuid]);
    }

    private static function createPolicy($platform, $def = 1)
    {
        $policyService = static::createClient()
            ->getContainer()
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