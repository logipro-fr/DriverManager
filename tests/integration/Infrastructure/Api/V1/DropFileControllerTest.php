<?php

namespace DriveManager\Tests\Integration\Infrastructure\Api\V1;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryDoctrine;
use DriveManager\Tests\WebBaseTestCase;
use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

use function Safe\json_encode;

class DropFileControllerTest extends WebBaseTestCase
{
    use DoctrineRepositoryTesterTrait;

    private string $API_KEY;
    private string $MAIL_ADDRESS;
    private FileRepositoryInterface $repository;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $apiKey = getenv('API_KEY_NEXTCLOUD');
        $mailAddress = getenv('MAIL_ADDRESS');
        if ($apiKey === false) {
            throw new RuntimeException('API_KEY environment variable is not set.');
        } elseif ($mailAddress === false) {
            throw new RuntimeException('MAIL_ADDRESS environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
            $this->MAIL_ADDRESS = $mailAddress;
        }

        $this->initDoctrineTester();
        $this->clearTables(["files"]);

        $this->client = static::createClient(["debug" => false]);

        /** @var FileRepositoryDoctrine $autoInjectedRepo */
        $autoInjectedRepo = $this->client->getContainer()->get("drivemanager.repository");
        $this->repository = $autoInjectedRepo;
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
                "filePathToDirectory" => "/test/BadFile.txt",
                "fileDate" => "",
                "fileContent" => "some content",
                "driver" => "badApiName",
            ])
        );
        $exceptedMessage = '"message":"A wrong api name is enter in paramater of function create"';

        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseStatus = $this->client->getResponse()->getStatusCode();

        $this->assertEquals(400, $responseStatus);
        $this->assertStringContainsString('"success":false', $responseContent);
        $this->assertStringContainsString('ErrorCode":"BadApiNameException"', $responseContent);
        $this->assertStringContainsString('"data":""', $responseContent);
        $this->assertStringContainsString($exceptedMessage, $responseContent);
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
                "fileToDeposit" => "testFile",
                "filePathToDirectory" => "Test/testFile.txt.txt",
                "fileDate" => "",
                "fileContent" => "some content",
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

        $this->assertArrayHasKey('ErrorCode', $responseData);
        $this->assertEquals('', $responseData['ErrorCode']);
        $this->assertStringContainsString('"ErrorCode":', $responseContent);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('fileId', $responseData['data']);
        $this->assertStringStartsWith('fil_', $responseData['data']['fileId']);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('', $responseData['message']);
        $this->assertStringContainsString('"message":""', $responseContent);
    }
}
