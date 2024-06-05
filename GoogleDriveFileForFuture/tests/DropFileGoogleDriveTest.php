<?php

namespace DriveManager\Tests\Infrastructure;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use PHPUnit\Framework\TestCase;
use DriveManager\Infrastructure\DriveProvider\DropFileGoogleDrive;
use DriveManager\Application\Service\DropFile\Exceptions\InvalideDataException;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\DriveProvider\RequestGoogleDriveApi;
use DriveManager\Infrastructure\DriveProvider\ResponseGoogleDriveApi;
use JsonException;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;
use function Safe\getcwd;
use function Safe\json_encode;

class DropFileGoogleDriveTest extends TestCase
{
    private const TEST_FILE = 'testfile.txt';
    private const PATH_ERROR = "DriveManager\Infrastructure\RequestGoogleDriveApi";
    public const PATH_UPLOAD_GOOGLE_DRIVE_FILE = 'https://www.googleapis.com/upload/drive/v3/files';


    private string $apiKey;

    public function setUp(): void
    {
        $fileApiKey = sprintf(getcwd() . DropFileGoogleDrive::PATH_RESOURCES, 'GoogleDriveApiKey.txt');
        $this->apiKey = file_get_contents($fileApiKey);
    }

    public function testHeaderStructure(): void
    {
        $client = $this->createMockHttpClient();
        $dropFileGoogleDrive = new DropFileGoogleDrive($client, $this->apiKey);

        $response = $dropFileGoogleDrive->paramHeader(new FileContent());
        $expectedHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        foreach ($expectedHeaders as $key => $value) {
            $this->assertArrayHasKey($key, $response['headers']);
            $this->assertEquals($value, $response['headers'][$key]);
        }
    }

    private function createMockHttpClient(FileName $filename = new FileName('testingFile')): MockHttpClient
    {
        $filePath = sprintf(getcwd() . DropFileGoogleDrive::PATH_RESOURCES, $filename->getFileName());
        if (!file_exists($filePath)) {
            $responses = [
                new MockResponse('', ['http_code' => 404])
            ];
        } else {
            $responseData = file_get_contents($filePath);
            $responses = [
                new MockResponse($responseData)
            ];
        }
        return new MockHttpClient($responses);
    }

    public function testHeaderContent(): void
    {
        $content = 'content to test';
        $client = $this->createMockHttpClient();
        $dropFileGoogleDrive = new DropFileGoogleDrive($client, $this->apiKey);

        $response = $dropFileGoogleDrive->paramHeader(new FileContent('content to test'));
        $this->assertEquals($content, $response['body']);
    }

    public function testDropFileSuccess(): void
    {
        $content = ['id' => '1234', 'name' => 'test.txt', 'mimeType' => DropFileGoogleDrive::MIME_TYPE];
        $capturedRequest = [];

        $client = $this->createMockedClientWithResponse($content, 200, $capturedRequest);

        $googleDriveTest = new DropFileGoogleDrive($client, $this->apiKey);
        $file = new File(new FileName(self::TEST_FILE), new Path(), new FileContent("Test content"));
        $googleDriveTest->dropFile($file, self::PATH_UPLOAD_GOOGLE_DRIVE_FILE);

        $actualKeys = [
            'metadata' => $capturedRequest['body']['metadata'],
            'data' => $capturedRequest['body']['data']
        ];

        $expectedKeys = [
            'metadata' => [
                'name' => self::TEST_FILE,
                'mimeType' => DropFileGoogleDrive::MIME_TYPE,
            ],
            'data' => 'Test content'
        ];

        $expectedUrl = self::PATH_UPLOAD_GOOGLE_DRIVE_FILE . '?uploadType=media';
        $this->assertEquals($expectedUrl, $capturedRequest['url']);
        $this->assertEquals('POST', $capturedRequest['method']);
        $this->assertEquals($expectedKeys, $actualKeys);
    }

    /**
     * @param array<string> $responseContent
     * @param array<string> $capturedRequest
     */
    private function createMockedClientWithResponse(
        array $responseContent,
        int $httpCode,
        array &$capturedRequest
    ): MockHttpClient {
        $responseJson = json_encode($responseContent);
        $expectedResponse = new MockResponse($responseJson, ['http_code' => $httpCode]);

        return new MockHttpClient(
            function ($method, $url, $options) use ($expectedResponse, &$capturedRequest) {
                $capturedRequest = [
                    'method' => $method,
                    'url' => $url,
                    'body' => json_decode($options['body'], true)
                ];
                return $expectedResponse;
            },
        );
    }

    // Tests json response file
    public function testRequestFileHello(): void
    {
        $fileName = new FileName('googleDriveCreateFileHello.json');
        $client = $this->createMockHttpClient($fileName);
        $googleDriveTest = new DropFileGoogleDrive($client, $this->apiKey);
        $requestApi = new RequestGoogleDriveApi("hello.txt", "hello content");
        $response = $googleDriveTest->request($requestApi, $fileName);

        $expectedValues = [
            'kind' => "drive#file",
            'id' => "1UQSp-VlBlS77mabXaHQgNctVojSmq7On",
            'name' => "hello.txt",
            'mimeType' => "text/plain"
        ];
        $this->assertGoogleDriveApiResponse($response, $expectedValues);
    }

    /**
     * @param array<string> $expectedValues
     */
    private function assertGoogleDriveApiResponse(ResponseGoogleDriveApi $response, array $expectedValues): void
    {
        $this->assertEquals($expectedValues['kind'], $response->kind);
        $this->assertEquals($expectedValues['id'], $response->id);
        $this->assertEquals($expectedValues['name'], $response->name);
        $this->assertEquals($expectedValues['mimeType'], $response->mimeType);
    }

    public function testRequestFileDevisPlombier(): void
    {
        $fileName = new FileName('googleDriveCreateFileDevisPlombier.json');
        $client = $this->createMockHttpClient($fileName);
        $googleDriveTest = new DropFileGoogleDrive($client, $this->apiKey);
        $requestApi = new RequestGoogleDriveApi("devisPlombier.pdf", "Devis d'une valeur de 1500€");
        $response = $googleDriveTest->request($requestApi, $fileName);

        $expectedValues = [
            'kind' => "drive#file",
            'id' => "1Pq4bcTxJz5KhiU3n0D6kZRH_r9IYO9o-",
            'name' => "devisPlombier.pdf",
            'mimeType' => "application/octet-stream"
        ];
        $this->assertGoogleDriveApiResponse($response, $expectedValues);
    }

    // Tests throws exception
    public function testRequestThrowsExceptionForUnexistingJson(): void
    {
        $client = $this->createMockHttpClient();
        $drive = new DropFileGoogleDrive($client, $this->apiKey);
        $requestApi = new RequestGoogleDriveApi("", "content");

        $this->expectException(FilesystemException::class);
        $this->expectExceptionMessageMatches("/.*: Failed to open stream: No such file or directory/");
        $drive->request($requestApi, new FileName('unexisting.json'));
    }

    public function testJsonDecodeThrowsJsonException(): void
    {
        $client = $this->createMockHttpClient();
        $drive = new DropFileGoogleDrive($client, $this->apiKey);

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage("Syntax error");

        $requestApi = new RequestGoogleDriveApi("invalidFile.txt", "content");
        $drive->request($requestApi, new FileName('invalidSyntax.json'));
    }

    public function testRequestThrowsExceptionForInvalidJson(): void
    {
        $client = $this->createMockHttpClient();
        $drive = new DropFileGoogleDrive($client, $this->apiKey);
        $requestApi = new RequestGoogleDriveApi(getcwd() . self::TEST_FILE, "content");

        $this->expectException(InvalideDataException::class);
        $this->expectExceptionMessage("JSON data is incomplete or invalid at " . self::PATH_ERROR);
        $drive->request($requestApi, new FileName('invalidContent.json'));
    }

    // Tests file existance
    public function testFileExists(): void
    {
        $client = $this->createMockHttpClient();
        $googleDriveTest = new DropFileGoogleDrive($client, $this->apiKey);
        $this->assertTrue($googleDriveTest->isFileExists(new FileName('myHello.txt')));
    }

    public function testFileDoesntExist(): void
    {
        $client = $this->createMockHttpClient(new FileName('UnexistingResponseFile.json'));
        $drive = new DropFileGoogleDrive($client, $this->apiKey);
        $this->assertFalse($drive->isFileExists(new FileName("UnexistingFile.txt")));
    }
}
