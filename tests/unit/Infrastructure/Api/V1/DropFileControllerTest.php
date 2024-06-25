<?php

namespace DriveManager\Tests\Infrastructure\Api\V1;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\Api\V1\DropFileController;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryDoctrine;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class DropFileControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    // private string $API_KEY;
    // private string $MAIL_ADDRESS;
    private FileRepositoryInterface $repository;
    private KernelBrowser $client;

    protected function setUp(): void
    {
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
                "fileToDeposit" => "testfile.txt",
                "filePathToDirectory" => "",
                "fileDate" => "",
                "fileContent" => "",
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
                "fileToDeposit" => "testfile.txt",
                "filePathToDirectory" => "",
                "fileDate" => "",
                "fileContent" => "",
                "driver" => "NextCloudMock",
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

    public function testDropFileController(): void
    {
        $vfs = vfsStream::setup('root');
        //$dropFile = new DropFileForFileSystem($vfs->url());
        $this->repository = new FileRepositoryInMemory();
        $controller = new DropFileController($this->repository, $this->getEntityManager());

        $request = Request::create(
            "/api/v1/dropFile/dropFile",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "fileId" => "",
                "fileToDeposit" => "testfile.txt",
                "filePathToDirectory" => "",
                "fileDate" => "",
                "fileContent" => "",
                "driver" => "NextCloudMock",
            ]),
        );
        $response = $controller->dropFile($request);
        /** @var string */
        $responseContent = $response->getContent();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringContainsString('"data":{"fileId":', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
