<?php

namespace DriveManager\Tests\Infrastructure;

use DateTimeImmutable;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\Response\MockResponse;

class DropFileProviderFactoryTest extends TestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';

    public function testProviderNextcloudMock(): void
    {
        // Arrange / Given
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $provider = $factory->create('NextCloudMock');
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest(
            "",
            "contrat-signed.pdf",
            "nextsign/contrat/",
            "contenu du pdf",
            "NextCloudMock"
        );
        $currentDate = new DateTimeImmutable();

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();

        // Assert / Then
        $this->assertInstanceOf(DropFileNextcloud::class, $provider);
        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $response->createdPath);
        $this->assertEquals("contrat-signed.pdf", $response->createdFileToDeposit);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
        //$this->assertEquals(200,$request-> ); //$response->getStatusCode()
    }

    public function testProviderNextcloud(): void
    {
        $factory = new DropFileProviderFactory(self::BASE_URI);

        $this->assertInstanceOf(DropFileNextcloud::class, $factory->create('NextCloud'));
    }

    public function testProviderFileSysteme(): void
    {
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $provider = $factory->create('FileSystem');

        $this->assertInstanceOf(DropFileForFileSystem::class, $provider);
    }
}
