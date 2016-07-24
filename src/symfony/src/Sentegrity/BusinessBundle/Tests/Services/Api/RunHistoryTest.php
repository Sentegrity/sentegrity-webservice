<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Api;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RunHistoryTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Api\RunHistory $runHistoryService */
    public static $runHistoryService;

    public static function setUpBeforeClass()
    {
        self::$runHistoryService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.api.run_history');
    }

    /**
     * @group api_run_history
     */
    public function testSave()
    {
        $runHistoryData = array(
            "user_activation_id" => 'test@domain.dfg',
            "organization_id" => 1,
            "device_salt" => 'qwerty',
            "phone_model" => 'iPhone 5s',
            "platform" => Platform::IOS,
            "objects" => ['key' => 'value'],
        );

        $rsp = self::$runHistoryService->save($runHistoryData);
        $this->assertTrue($rsp, "Save failed");
    }
}