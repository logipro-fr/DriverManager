<?php

namespace DriveManager\Tests\Application\Service\DropFile;

use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class DropFileTest extends TestCase
{
    private const PATH_RESOURCES = '/tests/unit/resources/%s';
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private const MAIL_ADDRESS = 'romain.malosse@logipro.com';
    private string $apiKey;

    public function testDepositASimpleFile(): void
    {
        $this->apiKey = file_get_contents(getcwd() . sprintf(self::PATH_RESOURCES, 'NextCloudApiKey.txt'));

        $factory = new DropFileProviderFactory(self::BASE_URI, self::MAIL_ADDRESS, $this->apiKey);
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

    /*public function testDepositAFileWithComplexPath(): void
    {
        // Arrange / Given
        $apiSpy = new DropFileApiVfs();
        $apiSpy->createDirectory('nextsign/contrat');
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($apiSpy, $repository);
        $request = new DropFileRequest("", "contrat-signed.pdf", "nextsign/contrat/", "", "contenu du pdf");

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();
        $fullPath = $response->createdPath . $response->createdFileToDeposit;

        // Assert / Then
        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $fullPath);
        $this->assertEquals(1, $apiSpy->count);
    }

    public function testCountFilesDeposit(): void
    {
        // Arrange / Given
        $apiSpy = new DropFileApiVfs();
        $apiSpy->createDirectory('nextsign/contrat');
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($apiSpy, $repository);
        $request = new DropFileRequest("", "contrat.pdf", "nextsign/", "", "contenu du contrat pdf");
        $request2 = new DropFileRequest("", "contrat2.pdf", "nextsign/", "", "contenu du contrat pdf");

        // Act / When
        $service->execute($request);
        $service->execute($request2);

        // Assert / Then
        $this->assertEquals(2, $apiSpy->count);
    }*/
}
