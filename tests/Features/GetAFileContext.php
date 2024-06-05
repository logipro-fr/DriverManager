<?php

namespace Features;

use Behat\Behat\Context\Context;
use DateTimeImmutable;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Application\Service\DropFile\DropFileResponse;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class GetAFileContext implements Context
{
    private vfsStreamDirectory $vfs;
    private DropFileResponse $response;
    private FileRepositoryInMemory $repository;
    private FileID $fileId;

        /**
     * @Given the identity of a file
     */
    public function theIdentityOfAFile(): void
    {
        $this->vfs = vfsStream::setup('root');
        $currentDate = new DateTimeImmutable();
        $request = new DropFileRequest(
            "unIdDeTest",
            "hello.txt",
            "chemin/de/test",
            $currentDate->format('Y-m-d H:i:s'),
            "some content"
        );
        $this->repository = new FileRepositoryInMemory();
        $dropFileService = new DropFileForFileSystem($this->vfs->url());
        $dropFileService->createDirectory("chemin/de/test");
        $service = new DropFile($dropFileService, $this->repository);

        $service->execute($request);
        $this->response = $service->getResponse();

        $this->debugVfsStream($this->vfs);
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


    /**
     * Debug function to print the virtual file system hierarchy.
     *
     * @param vfsStreamDirectory $directory The root directory of the virtual file system.
     * @param string $indent The indentation for the current level.
     */
    private function debugVfsStream(vfsStreamDirectory $directory, string $indent = ''): void
    {
        echo $indent . $directory->getName() . PHP_EOL;

        foreach ($directory->getChildren() as $child) {
            if ($child instanceof vfsStreamDirectory) {
                $this->debugVfsStream($child, $indent . '  ');
            } else {
                echo $indent . '  ' . $child->getName() . PHP_EOL;
            }
        }
    }
}
