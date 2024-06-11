<?php

namespace DriveManager\Tests\Application\Service\DropFile;

use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use DriveManager\Tests\BaseTestCase;

class DropFileTest extends BaseTestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private const MAIL_ADDRESS = 'romain.malosse@logipro.com';
    private string $API_KEY;

    public function setUp(): void
    {
        parent::setUp();
        $apiKey = getenv('API_KEY_NEXTCLOUD');
        if ($apiKey === false) {
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
    }

    public function testDepositASimpleFile(): void
    {
        $factory = new DropFileProviderFactory(self::BASE_URI, self::MAIL_ADDRESS, $this->API_KEY);
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest("", "hello.txt", "Test/", "", "hello", "NextCloudMock");

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();
        $expectedId = $response->createdFileId;
        $actualId = $repository->findById(new FileId($response->createdFileId))->getId();

        // Assert / Then
        $this->assertEquals("hello.txt", $response->createdFileToDeposit);
        $this->assertEquals($expectedId, $actualId);
        $this->assertEquals("Test/hello.txt", $response->createdPath . $response->createdFileToDeposit);
    }

    public function testDepositAFileWithComplexPath(): void
    {
        // Arrange / Given
        $apiVfs = new DropFileApiVfs();
        $apiVfs->createDirectory('nextsign/contrat');
        $factory = new DropFileProviderFactory(self::BASE_URI, self::MAIL_ADDRESS, $this->API_KEY);
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest(
            "",
            "contrat-signed.pdf",
            "nextsign/contrat/",
            "",
            "contenu du pdf",
            "NextCloudMock"
        );

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();
        $fullPath = $response->createdPath . $response->createdFileToDeposit;

        // Assert / Then
        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $fullPath);
    }

    public function testCountFilesDeposit(): void
    {
        // Arrange / Given
        $apiVfs = new DropFileApiVfs();
        $file = new File(new FileName("test"), new Path(), new FileContent("some content"));

        // Act / When
        $apiVfs->dropFile($file);
        $apiVfs->dropFile($file);

        // Assert / Then
        $this->assertEquals(2, $apiVfs->count);
    }
}
