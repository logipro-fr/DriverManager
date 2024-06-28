<?php

namespace DriveManager\Tests\Infrastructure\Api\V1;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\Api\V1\DropFileController;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryDoctrine;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class DropFileControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private FileRepositoryInterface $repository;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["files"]);

        $this->client = static::createClient(["debug" => false]);
    }

    public function testControllerBadApiNameException(): void
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
                "fileContent" => "",
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
                "fileToDeposit" => "testfile.txt",
                "filePathToDirectory" => "",
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


    public function testDropFileController(): void
    {
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


    public function testExecute(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name = DropFileControllerTest::class;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('flush');

        $entityManager
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $controller = new DropFileController(
            new FileRepositoryDoctrine($entityManager),
            $entityManager
        );

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
                "fileContent" => "",
                "driver" => "NextCloudMock",
            ]),
        );

        $response = $controller->dropFile($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
    }
}
