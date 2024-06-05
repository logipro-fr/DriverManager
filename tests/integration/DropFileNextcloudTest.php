<?php

namespace DriveManager\integration;

use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use DriveManager\Application\Service\DropFile\Exceptions\FailUploadingFileException;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;

use function Safe\file_get_contents;
use function Safe\getcwd;

class DropFileNextcloudTest extends TestCase
{
    private DropFileNextcloud $nextcloudClient;
    private string $apiKey;
    private const PATH_RESOURCES = '/tests/unit/resources/%s';
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private const PATH_ACCESS_NEXTCLOUD = 'files/romain.malosse@logipro.com/Test/';
    private Path $path;
    protected function setUp(): void
    {
        $this->apiKey = file_get_contents(getcwd() . sprintf(self::PATH_RESOURCES, 'NextCloudApiKey.txt'));
        $this->path = new Path(self::BASE_URI . self::PATH_ACCESS_NEXTCLOUD);
        $this->nextcloudClient = new DropFileNextcloud(
            self::BASE_URI,
            'romain.malosse@logipro.com',
            $this->apiKey,
            new CurlHttpClient([
                'auth_basic' => ['romain.malosse@logipro.com', $this->apiKey]
            ])
        );
    }

    public function testDropFileFail(): void
    {
        $this->expectException(FailUploadingFileException::class);
        $this->expectExceptionMessageMatches('/Échec du téléchargement : \d{3}/');

        $path = new Path("https://nue.loo.com/romsmolosse@logobi.com/");
        $file = new File(new FileName('nonexistentfile.txt'), $path, new FileContent(""));

        $this->nextcloudClient->dropFile($file);
    }

    public function testDropFileSuccess(): void
    {
        $fileName = 'testfile.txt';
        $fileContent = new FileContent(file_get_contents(getcwd() . "/tests/unit/resources/$fileName"));
        $file = new File(new FileName("$fileName"), new Path($this->path . "/$fileName"), $fileContent);
        $this->nextcloudClient->dropFile($file);

        $this->assertTrue(true, "File dropped successfully.");
    }

    public function testIsFileExistsTrue(): void
    {
        $file = new File(new FileName('hello.txt'), new Path($this->path . "/hello.txt"), new FileContent(''));

        $existingFile = $this->nextcloudClient->isFileExists($file);

        $this->assertTrue($existingFile);
    }

    public function testIsFileExistsFalse(): void
    {
        $fileName = 'nonexistentfile.txt';
        $file = new File(new FileName($fileName), new Path($this->path . "/$fileName"), new FileContent(''));

        $existingFile = $this->nextcloudClient->isFileExists($file);

        $this->assertFalse($existingFile);
    }

    public function testReadAFile(): void
    {
        $expectedContent = file_get_contents(getcwd() . '/tests/integration/resources/hello.txt');
        $file = new File(new FileName('hello.txt'), new Path($this->path . "/hello.txt"), new FileContent("Hello"));

        $fileContent = $this->nextcloudClient->readFile($file);

        $this->assertEquals($expectedContent, $fileContent);
    }
}
