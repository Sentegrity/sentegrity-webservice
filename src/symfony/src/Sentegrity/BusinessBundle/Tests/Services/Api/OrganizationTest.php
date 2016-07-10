<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Sentegrity\BusinessBundle\Services\Admin\Organization;
use Sentegrity\BusinessBundle\Services\Admin\Policy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sentegrity\BusinessBundle\Handlers\Platform;

class OrganizationTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Api\Organization $organizationService */
    public static $organizationService;
    public static $policyUuid;

    /**
     * @group api_organization
     */
    public static function setUpBeforeClass()
    {
        self::$organizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.organization');

        // create a test policy
        // use admin create policy method
        /** @var Policy $adminPolicyService */
        $adminPolicyService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.policy');

        $policyData = array(
            "name" => 'Test policy name',
            "platform" => Platform::IOS,
            "is_default" => 0,
            "app_version" => 'v1.0',
            "data" => ['key' => 'value']
        );
        $rsp = $adminPolicyService->create($policyData);
        self::$policyUuid = $rsp->data;
    }

    /**
     * @group api_organization
     */
    public function testGetDomainNameFromEmail()
    {
        $email = "test@email.test";
        $rsp = \Sentegrity\BusinessBundle\Services\Api\Organization::getDomainNameFromEmail($email);
        $this->assertEquals($rsp, 'email.test');
    }

    /**
     * @group api_organization
     */
    public function testGetOrganizationByDomainName()
    {
        /** @var Organization $adminOrganizationService */
        $adminOrganizationService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization');

        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domaintest.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => self::$policyUuid,
            "policy_android" => self::$policyUuid,
            "username" => "admin",
            "password" => "pass"
        );

        $uuid = $adminOrganizationService->create($organizationData);
        $orgId = $adminOrganizationService->getOrganizationIdByUuid($uuid->data);
        $rsp = self::$organizationService->getOrganizationByDomainName('test@domaintest.test');
        $this->assertEquals($rsp, $orgId);
    }
}