<?php

namespace DriveManager\Tests\Integration\Infrastructure\Api\V1;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class DropFileControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["files"]);

        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');

        $this->client = static::createClient(["debug" => false]);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/dropFile/dropFile",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "fileId" => "UnIdDeTest",
                "fileToDeposit" => "BadFile.txt",
                "filePathToDirectory" => "/test/",
                "fileDate" => "",
                "fileContent" => "some content",
                "driver" => "badApiName",
            ])
        );
        $expectedMessage = '"message":"A wrong api name is enter on parameter driver of function create"';

        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseStatus = $this->client->getResponse()->getStatusCode();

        $this->assertEquals(400, $responseStatus);
        $this->assertStringContainsString('"success":false', $responseContent);
        $this->assertStringContainsString('"statusCode":"BadApiNameException"', $responseContent);
        $this->assertStringContainsString('"data":[]', $responseContent); // Vérifie un tableau vide
        $this->assertStringContainsString($expectedMessage, $responseContent);
    }


    public function testControllerRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/dropFile/dropFile",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "fileId" => "UnIdDeTest",
                "fileToDeposit" => "testFile.txt",
                "filePathToDirectory" => "Test/",
                "fileDate" => "",
                "fileContent" => "some content for a test",
                "driver" => "NextCloud",
            ])
        );

        $responseContent = strval($this->client->getResponse()->getContent());
        $responseCode = $this->client->getResponse()->getStatusCode();
        $responseData = json_decode($responseContent, true);

        $this->assertResponseIsSuccessful();
        $this->assertEquals(201, $responseCode);
        $this->assertIsArray($responseData);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertStringContainsString('"success":true', $responseContent);

        $this->assertArrayHasKey('statusCode', $responseData);
        $this->assertEquals('', $responseData['statusCode']);
        $this->assertStringContainsString('"statusCode":', $responseContent);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('fileId', $responseData['data']);
        $this->assertStringStartsWith('fil_', $responseData['data']['fileId']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('', $responseData['message']);
        $this->assertStringContainsString('"message":""', $responseContent);
    }
}
