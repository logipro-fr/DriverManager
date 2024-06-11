<?php

namespace DriveManager\Tests\Infrastructure\DriveProvider;

//require 'vendor/autoload.php';

use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use DriveManager\Application\Service\DropFile\Exceptions\FailUploadingFileException;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\MockHttpClient;

// use Dotenv\Dotenv;

// // Définis le chemin correct vers ton fichier .env.local
// $dotenv = Dotenv::createImmutable(getcwd().'src/Infrastructure/Shared/Symfony/.env.local');
// $dotenv->load();

class DropFileNextcloudTest extends TestCase
{
    private const UPLOAD_FILE_PATH = 'files/romain.malosse@logipro.com/Test/';
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private const MAIL_ADDRESS = 'romain.malosse@logipro.com';
    private DropFileNextcloud $nextcloudClient;
    /**
     * @var array<int, array{method: string, url: string, options: array<string, mixed>}>
     */
    private array $capturedRequests = [];
    private string $API_KEY_NEXTCLOUD;

    protected function setUp(): void
    {
        $this->API_KEY_NEXTCLOUD = getenv('API_KEY_NEXTCLOUD');
        if ($this->API_KEY_NEXTCLOUD === false) {
            throw new \RuntimeException('API_KEY is not set in the environment variables.');
        }

        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD
        );
    }

    public function testDropFileFail(): void
    {
        $this->expectException(FailUploadingFileException::class);
        $this->expectExceptionMessageMatches('/Download failed : \d{3}/');

        $client = new MockHttpClient(new MockResponse('', ['http_code' => 400]));
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );
        $file = new File(new FileName('testfile.txt'), new Path('Bad/link/testfile.txt'));

        $this->nextcloudClient->dropFile($file);
    }

    public function testDropFileSuccess(): void
    {
        $path = new Path(self::UPLOAD_FILE_PATH . "hello.txt");
        $file = new File(new FileName('hello.txt'), $path, new FileContent("Hello"));

        // Two mock responses: one for the PUT request and one for the HEAD request.
        $responses = [
            new MockResponse('', ['http_code' => 200]),  // Response for the PUT request
            new MockResponse('', ['http_code' => 200]),  // Response for the HEAD request
        ];
        $client = new MockHttpClient($responses);

        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );

        $this->nextcloudClient->dropFile($file);

        $this->assertTrue($this->nextcloudClient->isFileExists($file));
    }

    public function testDropBody(): void
    {
        $file = new File(new FileName('hello.txt'));
        $client = new MockHttpClient(function ($method, $url, $options) {
            $this->capturedRequests[] = ['method' => $method, 'url' => $url, 'options' => $options];
            return new MockResponse('', ['http_code' => 200]);
        });
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );

        $this->nextcloudClient->dropFile($file);
        $lastRequest = end($this->capturedRequests);

        if (
            $lastRequest !== false &&
            is_array($lastRequest['options']) &&
            array_key_exists('body', $lastRequest['options'])
        ) {
            $this->assertEquals($file->getContent(), $lastRequest['options']['body']);
        } else {
            $this->fail('No valid request captured or missing "options" or "body" key.');
        }
    }

    public function testIsFileExistsTrue(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 200]));
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );

        $file = new File(new FileName('testfile.txt'));

        $existingFile = $this->nextcloudClient->isFileExists($file);

        $this->assertTrue($existingFile);
    }

    public function testIsFileExistsFalse(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 400]));
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );

        $file = new File(new FileName('noneExistingFile.txt'), new Path('noneExistingDir/'));

        $existingFile = $this->nextcloudClient->isFileExists($file);

        $this->assertFalse($existingFile);
    }

    public function testReadAFile(): void
    {
        $file = new File(new FileName('hello.txt'), new Path(), new FileContent("Hello"));
        $mockResponse = new MockResponse($file->getContent());
        $client = new MockHttpClient($mockResponse);
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            self::MAIL_ADDRESS,
            $this->API_KEY_NEXTCLOUD,
            $client
        );

        $fileContent = $this->nextcloudClient->readFile($file);

        $this->assertEquals('Hello', $fileContent);
    }
}
