<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Sentegrity\BusinessBundle\Services\Admin\Organization;
use Sentegrity\BusinessBundle\Services\Admin\Policy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Sentegrity\BusinessBundle\Handlers\Platform;

class PolicyTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Api\Policy $policyService */
    public static $policyService;
    public static $policyUuid;
    public static $policyId;

    public static function setUpBeforeClass()
    {
        self::$policyService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.policy');

        // create a test policy
        // use admin create policy method
        /** @var Policy $adminPolicyService */
        $adminPolicyService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.policy');

        $policyData = array(
            "name" => 'Test policy name abc',
            "platform" => Platform::IOS,
            "is_default" => 0,
            "app_version" => 'v1.0',
            "data" => ['key' => 'value']
        );
        $rsp = $adminPolicyService->create($policyData);
        self::$policyUuid = $rsp->data;

        $policy = $adminPolicyService->getPolicyByUuid(self::$policyUuid);
        self::$policyId = $policy->getId();
    }

    /**
     * @group api_policy
     */
    public function testCheckIfDefault()
    {
        $rsp = self::$policyService->checkIfDefault('Test policy name abc', Platform::IOS, 0, 'v0.1');
        $this->assertFalse($rsp);
    }

    /**
     * @group api_policy
     */
    public function testGetPolicyIdByGroupOrganizationPlatform()
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
            "policy_ios" => self::$policyUuid,
            "policy_android" => self::$policyUuid,
            "username" => "admin",
            "password" => "pass"
        );
        
        $uuid = $adminOrganizationService->create($organizationData);
        $orgId = $adminOrganizationService->getOrganizationIdByUuid($uuid->data);
        
        $rsp = self::$policyService->getPolicyIdByGroupOrganizationPlatform(
            0,
            $orgId,
            Platform::IOS
        );
        $this->assertEquals($rsp, self::$policyId, "Wrong id returned");
    }

    /**
     * @group api_policy
     */
    public function testGetNewPolicyRevision()
    {
        /** @var Policy $adminPolicyService */
        $adminPolicyService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.policy');

        $policyData = array(
            "name" => 'Test policy name',
            "platform" => Platform::IOS,
            "is_default" => 0,
            "app_version" => 'v1.0',
            "data" => ['key' => 'value'],
            "uuid" => self::$policyUuid
        );
        $adminPolicyService->update($policyData);

        $rsp = self::$policyService->getNewPolicyRevision(self::$policyId, 1, Platform::IOS);
        $this->assertEquals($rsp->key, $policyData['data']['key'], "New policy not fetched");
    }

    /**
     * @group api_policy
     */
    public function testGetPolicyById()
    {
        $rsp = self::$policyService->getPolicyById(self::$policyId);
        $rsp = $rsp['data'];
        $this->assertEquals($rsp->key, 'value', "Policy not fetched");
    }
}