<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\Exceptions\RepositoryDoesNotExistException;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileName;

class DropFileForFileSystem implements DropFileInterface
{
    private const PATH_REPOSITORY = '%s/%s';
    private const FULL_PATH = '%s/%s';
    public function __construct(private string $rootPath)
    {
    }

    public function dropFile(File $file): void
    {

        $pathToUpload = $file->getPath();
        $pathDirectory = sprintf(self::PATH_REPOSITORY, $this->rootPath, $pathToUpload);
        if (!is_dir($pathDirectory)) {
            throw new RepositoryDoesNotExistException("Repository ($pathToUpload) doesn't exist.");
        }
        $filePath = sprintf(self::FULL_PATH, $pathDirectory, $file->getFileName());
        file_put_contents($filePath, $file->getContent());
    }

    public function isFileExists(File $file): bool
    {
        return file_exists($this->rootPath . '/' . $file->getFileName());
    }

    public function createDirectory(string $directoryName): void
    {
        $fullPath = $this->rootPath . '/' . $directoryName;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
            //vfsStream::newDirectory($directoryName, 0777)->at(vfsStream::setup($this->rootPath));
        }
    }
}
