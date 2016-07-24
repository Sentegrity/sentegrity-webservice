<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Admin;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Sentegrity\BusinessBundle\Tests\Services\Utility;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SignInTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Admin\SignIn $signInService */
    public static $signInService;

    public static function setUpBeforeClass()
    {
        self::$signInService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.sign_in');

        // first we need to create two new policies, both for iOS and Android
        Utility::init(static::createClient()->getContainer());

        $organizationData = array(
            "name" => 'Test organization name',
            "domain_name" => 'domain.test',
            "contact_name" => 'Contact Name',
            "contact_email" => 'contact.email@domain.test',
            "contact_phone" => '+1 234 5678',
            "policy_ios" => Utility::createPolicy(Platform::IOS),
            "policy_android" => Utility::createPolicy(Platform::ANDROID),
            "username" => "admin",
            "password" => "pass"
        );

        static::createClient()
            ->getContainer()
            ->get('sentegrity_business.organization')
            ->create($organizationData);
    }

    /**
     * @group admin_sign_in
     */
    public function testSignInAndSignOut()
    {
        $userData = [
            "username" => "admin",
            "password" => "pass"
        ];

        $rsp = self::$signInService->signIn($userData);
        $this->assertTrue($rsp->successful, "Sign In failed");
        $this->assertArrayHasKey('token', $rsp->data, "Token should be returned as part of response");
        $this->assertArrayHasKey('permission', $rsp->data, "Token should be returned as part of response");

        // now test sign out
        $rsp = self::$signInService->signOut($rsp->data);
        $this->assertTrue($rsp->successful, "Sign Out failed");
    }
}