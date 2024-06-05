<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Application\Service\DropFile\DropFileResponse;
use DriveManager\Infrastructure\Api\V1\DropFileController;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class DropAFileContext implements Context
{
    private vfsStreamDirectory $vfs;
    private DropFileResponse $response;
    private FileRepositoryInMemory $repository;
    private string $fullPathName;

    /**
     * @Given the drive is NextCloudMock
     */
    public function theDriveIsNextcloudmock()
    {
        throw new PendingException();
    }

    /**
     * @Given the directory :directory exists :doesExist
     */
    public function theDirectoryExists(string $directory, string $doesExist): void
    {
        $this->vfs = vfsStream::setup('root');
        if ($doesExist === "yes") {
            vfsStream::newDirectory($directory)->at($this->vfs);
        }
    }

    /**
     * @When I deposit a file :fileToDeposit in the directory :directoryName
     */
    public function iDepositAFileInTheDirectory(string $fileToDeposit, string $directoryName): void
    {
        $this->fullPathName = "$directoryName"; //   /$fileToDeposit
        $currentDate = new \DateTimeImmutable();

        // ds fct controller
        $request = new DropFileRequest(
            "unIdDeTest",
            $fileToDeposit,
            $directoryName,
            $currentDate->format('Y-m-d H:i:s'),
            "some content",
            "NextCloudMock"
        );
        $this->repository = new FileRepositoryInMemory();
        $dropFileService = new DropFileForFileSystem($this->vfs->url());
        $dropFileService->createDirectory($directoryName);
        $service = new DropFile($dropFileService, $this->repository);
        $service->execute($request);
        $this->response = $service->getResponse();
        $controllerDropFile = new DropFileController($dropFileService, $this->repository);
        $this->response = $controllerDropFile->dropFile($request);
    }

    /**
     * @Then the file :fileToDeposit should be listed in the directory :directoryName
     */
    public function theFileShouldBeListedInTheDirectory(string $fileToDeposit, string $directoryName): void
    {
        // $path = $this->response->createdFilePath;
        // $fullPath = vfsStream::url("root/$path");
        $fullPath = vfsStream::url("root/$directoryName/$fileToDeposit");
        Assert::assertTrue(file_exists($fullPath));
    }

    /**
     * @Then the file has its own identity
     */
    public function theFileHasItsOwnIdentity(): void
    {
        Assert::assertNotEmpty($this->response->createdFileId);
        Assert::assertNotNull($this->response->createdFileId);
    }

    /**
     * @Then the file has create and updated dates
     */
    public function theFileHasCreateAndUpdatedDates(): void
    {
        $currentDate = new \DateTimeImmutable();
        $formattedDate = $currentDate->format('Y-m-d H:i:s');

        Assert::assertNotEmpty($this->response->createdDate);
        Assert::assertNotNull($this->response->createdDate);
        Assert::assertEquals($this->response->createdDate, $formattedDate);
    }

    /**
     * @Then the file knows its fullname path
     */
    public function theFileKnowsItsFullnamePath(): void
    {
        Assert::assertNotEmpty($this->response->createdPath);
        Assert::assertNotNull($this->response->createdPath);
        Assert::assertEquals($this->response->createdPath, $this->fullPathName);
    }
}
