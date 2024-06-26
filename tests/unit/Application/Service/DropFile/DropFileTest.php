<?php

namespace DriveManager\Tests\Application\Service\DropFile;

use DateTimeImmutable;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class DropFileTest extends TestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';

    public function testDepositASimpleFile(): void
    {
        // Arrange / Given
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest("", "hello.txt", "Test/", "hello", "NextCloudMock");
        $currentDate = new DateTimeImmutable();

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();
        $expectedId = $response->createdFileId;
        $actualId = $repository->findById(new FileId($response->createdFileId))->getId();

        // Assert / Then
        $this->assertEquals("hello.txt", $response->createdFileToDeposit);
        $this->assertEquals("Test/hello.txt", $response->createdPath);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
        $this->assertEquals($expectedId, $actualId);
    }

    public function testDepositAFileWithComplexPath(): void
    {
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $currentDate = new DateTimeImmutable();
        $request = new DropFileRequest(
            "",
            "contrat-signed.pdf",
            "nextsign/contrat/",
            "contenu du pdf",
            "NextCloudMock"
        );

        $service->execute($request);
        $response = $service->getResponse();

        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $response->createdPath);
        $this->assertEquals("contrat-signed.pdf", $response->createdFileToDeposit);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
    }

    public function testDepositASimpleFileWithFileSystem(): void
    {
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest("", "hello.txt", "Test/", "hello", "FileSystem");
        $currentDate = new DateTimeImmutable();

        $service->execute($request);
        $response = $service->getResponse();
        $expectedId = $response->createdFileId;
        $actualId = $repository->findById(new FileId($response->createdFileId))->getId();

        $this->assertEquals("hello.txt", $response->createdFileToDeposit);
        $this->assertEquals("Test/hello.txt", $response->createdPath);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
        $this->assertEquals($expectedId, $actualId);
    }

    public function testCountFilesDeposit(): void
    {
        $apiVfs = new DropFileApiVfs();
        $file = new File(new FileName("test"), new Path(), new FileContent("some content"));

        $apiVfs->dropFile($file);
        $apiVfs->dropFile($file);

        $this->assertEquals(2, $apiVfs->count);
    }
}
