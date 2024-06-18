<?php

namespace DriveManager\Tests\Infrastructure;

use DateTimeImmutable;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DropFileProviderFactoryTest extends TestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private string $API_KEY;
    private string $MAIL_ADDRESS;

    protected function setUp(): void
    {
        parent::setUp();
        $apiKey = getenv('API_KEY_NEXTCLOUD');
        $mailAddress = getenv('MAIL_ADDRESS');
        if ($apiKey === false) {
            throw new RuntimeException('API_KEY environment variable is not set.');
        } elseif ($mailAddress === false) {
            throw new RuntimeException('MAIL_ADDRESS environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
            $this->MAIL_ADDRESS = $mailAddress;
        }
    }

    public function testProviderNextcloudMock(): void
    {
        // Arrange / Given
        $factory = new DropFileProviderFactory(self::BASE_URI, $this->MAIL_ADDRESS, $this->API_KEY);
        $provider = $factory->create('NextCloudMock');
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
        $currentDate = new DateTimeImmutable();

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();

        // Assert / Then
        $this->assertInstanceOf(DropFileNextcloud::class, $provider);
        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $response->createdPath);
        $this->assertEquals("contrat-signed.pdf", $response->createdFileToDeposit);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
    }

    public function testProviderNextcloud(): void
    {
        // Arrange / Given
        $factory = new DropFileProviderFactory(self::BASE_URI, $this->MAIL_ADDRESS, $this->API_KEY);
        
        // Assert / Then
        $this->assertInstanceOf(DropFileNextcloud::class, $factory->create('NextCloud'));
    }

    public function testProviderFileSysteme(): void
    {
        // Arrange / Given
        $factory = new DropFileProviderFactory(self::BASE_URI, $this->MAIL_ADDRESS, $this->API_KEY);
        /** @var DropFileForFileSystem $provider */
        $provider = $factory->create('FileSysteme');
        
        // Assurez-vous que le provider est bien de type DropFileForFileSystem
        $this->assertInstanceOf(DropFileForFileSystem::class, $provider);
                
        $repository = new FileRepositoryInMemory();
        $service = new DropFile($factory, $repository);
        $request = new DropFileRequest(
            "",
            "contrat-signed.pdf",
            "nextsign/contrat/",
            "",
            "contenu du pdf",
            "FileSysteme"
        );
        $currentDate = new DateTimeImmutable();
        $provider->createDirectory('nextsign/contrat/');

        // Act / When
        $service->execute($request);
        $response = $service->getResponse();
    
        // Assert / Then
        $this->assertInstanceOf(DropFileForFileSystem::class, $provider);
        $this->assertEquals("nextsign/contrat/contrat-signed.pdf", $response->createdPath);
        $this->assertEquals("contrat-signed.pdf", $response->createdFileToDeposit);
        $this->assertEquals($currentDate->format('Y-m-d H:i:s'), $response->createdDate);
    }    
}
