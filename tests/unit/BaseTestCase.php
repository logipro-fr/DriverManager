<?php

namespace DriveManager\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $dotenv = new Dotenv();
        $dotenv->usePutenv();
        $dotenv->load(__DIR__ . '/../.env.test');

        $apiKey = getenv('API_KEY_NEXTCLOUD');
        $apiKey = getenv('MAIL_ADDRESS');
    }
}
