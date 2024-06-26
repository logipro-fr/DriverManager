<?php

namespace Features;

use Behat\Behat\Context\Context;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Application\Service\DropFile\DropFileResponse;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Assert;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Defines application features from the specific context.
 */
class DropAFileContext implements Context
{
    private vfsStreamDirectory $vfs;
    private DropFileResponse $response;
    private string $driverName;
    private string $fullPathName;

    /**
     * @Given I have set up the credentials
     */
    public function iHaveSetUpTheCredentials(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env');
    }

    /**
     * @Given the drive is :driver
     */
    public function theDriveIs(string $driver): void
    {
        $this->driverName = $driver;
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
        $currentDate = new \DateTimeImmutable();
        $request = new DropFileRequest(
            "unIdDeTest",
            "$fileToDeposit",
            "$directoryName/",
            "some content",
            "$this->driverName"
        );
        $dropFileProviderFactory = new DropFileProviderFactory("baseUriForProvider");

        $repository = new FileRepositoryInMemory();
        $service = new DropFile($dropFileProviderFactory, $repository);

        $service->execute($request);
        $this->response = $service->getResponse();
        $this->fullPathName = $this->response->createdPath;
    }

    /**
     * @Then the file :fileToDeposit should be listed in the directory :directoryName
     */
    public function theFileShouldBeListedInTheDirectory(string $fileToDeposit, string $directoryName): void
    {
        Assert::assertEquals("$directoryName/$fileToDeposit", $this->response->createdPath);
    }

    /**
     * @Then the file has its own identity
     */
    public function theFileHasItsOwnIdentity(): void
    {
        Assert::assertNotEmpty($this->response->createdFileId);
        Assert::assertNotNull($this->response->createdFileId);
        Assert::assertStringStartsWith('fil_', $this->response->createdFileId);
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
