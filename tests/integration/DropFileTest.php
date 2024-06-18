<?php

namespace DriveManager\Tests\integration;

use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DropFileTest extends TestCase
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/files/romain.malosse@logipro.com/';
    private string $API_KEY = "AlFi5GoZI^";
    private string $MAIL_ADDRESS = "romain.malosse@logipro.com";
    
    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     $apiKey = getenv('API_KEY_NEXTCLOUD');
    //     $mailAddress = getenv('MAIL_ADDRESS');
    //     if ($apiKey === false) {
    //         throw new RuntimeException('API_KEY environment variable is not set.');
    //     } elseif ($mailAddress === false) {
    //         throw new RuntimeException('MAIL_ADDRESS environment variable is not set.');
    //     } else {
    //         $this->API_KEY = $apiKey;
    //         $this->MAIL_ADDRESS = $mailAddress;
    //     }
    //     var_dump($this->API_KEY);
    //     var_dump($this->MAIL_ADDRESS);
    //     var_dump("coucou");
    // }

    public function testDropOneFile(): void
    {
        $repository = new FileRepositoryInMemory();
        $factory = new DropFileProviderFactory(self::BASE_URI, $this->MAIL_ADDRESS, $this->API_KEY);
        $request = new DropFileRequest("unId", "testFile.txt", "Test/", "", "some content", "NextCloud");
        $service = new DropFile($factory, $repository);

        $service->execute($request);
        $response = $service->getResponse();
        $file = $repository->findById(new FileId($response->createdFileId));
        $responseMessage = $file->getContent();

        $this->assertEquals(true, is_string($responseMessage));
    }
}