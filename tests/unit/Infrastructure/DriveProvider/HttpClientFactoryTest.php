<?php

namespace DriveManager\Tests\Infrastructure\DriveProvider;

use DriveManager\Infrastructure\DriveProvider\HttpClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ReflectionClass;

class HttpClientFactoryTest extends TestCase
{
    private HttpClientInterface $client;

    public function testCreate(): void
    {
        $baseUri = 'https://nextcloud.example.com';
        $username = 'emailAddress';
        $password = 'psw';
        $factory = new HttpClientFactory();
        $this->client = $factory->create($baseUri, $username, $password);
        $reflectionClass = new ReflectionClass($this->client);
        $property = $reflectionClass->getProperty('defaultOptions');
        $defaultOptions = $property->getValue($this->client);
        $defaultOptions = (array) $defaultOptions;

        $this->assertInstanceOf(HttpClientInterface::class, $this->client);
        $this->assertInstanceOf(CurlHttpClient::class, $this->client);
        $this->assertArrayHasKey('base_uri', $defaultOptions);
        $this->assertEquals($baseUri, $defaultOptions['base_uri']);
        $this->assertArrayHasKey('auth_basic', $defaultOptions);
        $this->assertEquals("$username:$password", $defaultOptions['auth_basic']);
        $this->assertArrayHasKey('headers', $defaultOptions);
        $this->assertIsArray($defaultOptions['headers']);
        $this->assertCount(2, $defaultOptions['headers']);
        $this->assertStringContainsString('OCS-APIRequest', $defaultOptions['headers'][0]);
        $this->assertStringContainsString('true', $defaultOptions['headers'][0]);
        $this->assertStringContainsString('Accept', $defaultOptions['headers'][1]);
        $this->assertStringContainsString('application/json', $defaultOptions['headers'][1]);
    }
}
