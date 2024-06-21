<?php

namespace DriveManager\Tests\integration;

use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class DropFileTest extends TestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/files/romain.malosse@logipro.com/';

    public function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
    }

    public function testDropOneFile(): void
    {
        $repository = new FileRepositoryInMemory();
        $factory = new DropFileProviderFactory(self::BASE_URI);
        $request = new DropFileRequest("unId", "testFile.txt", "Test/", "", "some content", "NextCloud");
        $service = new DropFile($factory, $repository);

        $service->execute($request);
        $response = $service->getResponse();
        $file = $repository->findById(new FileId($response->createdFileId));
        $responseMessage = $file->getContent();

        $this->assertEquals(true, is_string($responseMessage));
    }
}
