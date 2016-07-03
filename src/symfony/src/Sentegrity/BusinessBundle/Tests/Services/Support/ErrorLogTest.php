<?php
namespace Sentegrity\BusinessBundle\Tests\Services\Support;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ErrorLogTest extends WebTestCase
{
    /** @var \Sentegrity\BusinessBundle\Services\Support\ErrorLog $errorLogService */
    public static $errorLogService;

    public static function setUpBeforeClass()
    {
        self::$errorLogService = static::createClient()
            ->getContainer()
            ->get('sentegrity_business.error_log');
    }

    public function testWrite()
    {
        $fakeErrorText = "Some error occurred";
        $fakeErrorType = 1;

        $rsp = self::$errorLogService->write($fakeErrorText, $fakeErrorType);
        $this->assertTrue($rsp, "Error log write failed");
    }
}