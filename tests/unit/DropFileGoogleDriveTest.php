<?php

namespace DriveManager\Tests;

use DriveManager\DropFileGoogleDrive;
use PHPUnit\Framework\TestCase;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;

class DropFileGoogleDriveTest extends TestCase
{
    const RESPONSE_PATH = '/resources/googleDriveCreateFileHello.json';

    public function testDropFileGoogleDrive(): void
    {
        $clientMock = $this->createMock(GoogleClient::class);
        $driveServiceMock = $this->createMock(GoogleDrive::class);
        $filesMock = $this->createMock(GoogleDrive\Resource\Files::class);
        $driveServiceMock->files = $filesMock;

        $jsonPath = __DIR__ . self::RESPONSE_PATH;
        $jsonData = file_get_contents($jsonPath);
        $fileData = json_decode($jsonData, true);

        $expectedResponse = new DriveFile($fileData);

        $filesMock->expects($this->once())
                  ->method('create')
                  ->willReturn($expectedResponse);

        $drive = new DropFileGoogleDrive($clientMock, $driveServiceMock);
        $drive->drop("hello.txt", "hello");
        $this->assertTrue($drive->isFileExists("hello.txt"));
    }

    public function testFileDoesntExist(): void
    {
        $drive = new DropFileGoogleDrive();
        $this->assertFalse($drive->isFileExists("UnexistingFile.txt"));
    }
}
