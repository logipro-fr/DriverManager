<?php

namespace DriveManager\Tests\Infrastructure\Api\V1;

use DriveManager\Infrastructure\Api\V1\DropFileController;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\getcwd;
use function Safe\json_encode;

class DropFileControllerTest extends WebTestCase
{
    public function testControllerException(): void
    {
        $client = static::createClient();
        $client->request(
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
        /** @var string */
        $responseContent = $client->getResponse()->getContent();
        $responseStatus = $client->getResponse()->getStatusCode();

        $this->assertEquals(400, $responseStatus);
        $this->assertStringContainsString('"success":false', $responseContent);
        $this->assertStringContainsString('ErrorCode":"BadApiNameException"', $responseContent);
        $this->assertStringContainsString('"data":"', $responseContent);
        $this->assertStringContainsString('"message":"A wrong api name is enter in paramater of function create"', $responseContent);
    }

    public function testControllerRouting(): void
    {
        $client = static::createClient();
        $client->request(
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
        /** @var string */
        $responseContent = $client->getResponse()->getContent();
        $responseCode = $client->getResponse()->getStatusCode();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(201, $responseCode);
        $this->assertStringContainsString('"ErrorCode":', $responseContent);
        //$this->assertStringContainsString('"postId":"pos_', $responseContent);
        //$this->assertStringContainsString('"socialNetworks":"simpleBlog', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }

    public function testDropFileController(): void
    {
        $vfs = vfsStream::setup('root');
        $dropFile = new DropFileForFileSystem($vfs->url());
        $repository = new FileRepositoryInMemory();
        $controller = new DropFileController($repository);

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
