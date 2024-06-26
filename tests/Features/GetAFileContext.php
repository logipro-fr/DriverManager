<?php

namespace Features;

use Behat\Behat\Context\Context;
use DateTimeImmutable;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Application\Service\DropFile\DropFileResponse;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class GetAFileContext implements Context
{
    private DropFileResponse $response;
    private FileRepositoryInMemory $repository;
    private FileId $fileId;

    /**
     * @Given the identity of a file
     */
    public function theIdentityOfAFile(): void
    {
        $currentDate = new DateTimeImmutable();
        $request = new DropFileRequest(
            "unIdDeTest",
            "hello.txt",
            "chemin/de/test/",
            "some content",
            "FileSystem"
        );
        $this->repository = new FileRepositoryInMemory();
        $dropFileProviderFactory = new DropFileProviderFactory("fileSystemUri");

        $service = new DropFile($dropFileProviderFactory, $this->repository);
        $service->execute($request);
        $this->response = $service->getResponse();
    }

    /**
     * @When I get the file on its drive
     */
    public function iGetTheFileOnItsDrive(): void
    {
        $this->fileId = new FileId($this->response->createdFileId);
        Assert::assertEquals("hello.txt", $this->repository->findById($this->fileId)->getFileName());
    }

    /**
     * @Then the file has a content
     */
    public function theFileHasAContent(): void
    {
        Assert::assertEquals("some content", $this->repository->findById($this->fileId)->getContent());
    }
}
