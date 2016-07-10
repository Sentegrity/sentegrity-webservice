<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Admin;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Sentegrity\BusinessBundle\Services\Admin\Organization;
use Sentegrity\BusinessBundle\Tests\Services\Utility;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GroupTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Admin\Group $groupService */
    public static $groupService;

    public static $iosUuid;
    public static $androidUuid;

    public static function setUpBeforeClass()
    {
        self::$groupService= static::createClient()
            ->getContainer()
            ->get('sentegrity_business.group');

        // first we need to create two new policies, both for iOS and Android
        Utility::init(static::createClient()->getContainer());
        Utility::setUserSession();
        self::$iosUuid = Utility::createPolicy(Platform::IOS);
        self::$androidUuid = Utility::createPolicy(Platform::ANDROID);
    }

    /**
     * @group admin_group
     */
    public function testGetGroupByGroupAndOrganization()
    {
        /** @var Organization $adminOrganizationService */
        $adminOrganizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization');

        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domain.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => self::$iosUuid,
            "policy_android" => self::$androidUuid,
            "username" => "admin",
            "password" => "pass"
        );

        $uuid = $adminOrganizationService->create($organizationData);
        $org = $adminOrganizationService->getOrganizationByUuid($uuid->data);
        
        $rsp = self::$groupService->getGroupByGroupAndOrganization(0, $org);
        $this->assertInstanceOf('\Sentegrity\BusinessBundle\Entity\Documents\Groups', $rsp);
        $this->assertEquals($rsp->getOrganization()->getUuid(), $uuid->data);
    }

    /**
     * @group admin_group
     */
    public function testDeleteGroupsByOrganization()
    {
        /** @var Organization $adminOrganizationService */
        $adminOrganizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization');

        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domain.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => self::$iosUuid,
            "policy_android" => self::$androidUuid,
            "username" => "admin",
            "password" => "pass"
        );

        $uuid = $adminOrganizationService->create($organizationData);
        $org = $adminOrganizationService->getOrganizationByUuid($uuid->data);

        $rsp = self::$groupService->deleteGroupsByOrganization($org);
        $this->assertEquals($rsp, 1, "Delete failed");
    }

    /**
     * @group admin_group
     */
    public function testGetDefaultGroupsByMultipleOrganizations()
    {
        /** @var Organization $adminOrganizationService */
        $adminOrganizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization');

        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domain.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => self::$iosUuid,
            "policy_android" => self::$androidUuid,
            "username" => "admin",
            "password" => "pass"
        );

        $uuid = $adminOrganizationService->create($organizationData);
        $org[] = $adminOrganizationService->getOrganizationByUuid($uuid->data);
        $uuid = $adminOrganizationService->create($organizationData);
        $org[] = $adminOrganizationService->getOrganizationByUuid($uuid->data);
        $uuid = $adminOrganizationService->create($organizationData);
        $org[] = $adminOrganizationService->getOrganizationByUuid($uuid->data);

        $rsp = self::$groupService->getDefaultGroupsByMultipleOrganizations($org);
        $this->assertCount(3, $rsp, "Wrong number of groups returned");
    }
}