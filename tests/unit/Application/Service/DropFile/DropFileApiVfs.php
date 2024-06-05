<?php

namespace DriveManager\Tests\Application\Service\DropFile;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\Exceptions\RepositoryDoesNotExistException;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileName;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class DropFileApiVfs implements DropFileInterface
{
    public int $count = 0;
    private vfsStreamDirectory $vfsRoot;

    public function __construct()
    {
        $this->vfsRoot = vfsStream::setup('root');
    }

    public function dropFile(File $file): void
    {
        $pathToUpload = $file->getPath();
        $pathRepository = $this->vfsRoot->url() . '/' . $pathToUpload;
        if (!is_dir($pathRepository)) {
            throw new RepositoryDoesNotExistException("Repository ($pathToUpload) doesn't exist.");
        }

        $filePath = $pathRepository . '/' . $file->getFileName();
        file_put_contents($filePath, $file->getContent());
        $this->count += 1;
    }

    public function isFileExists(File $file): bool
    {
        return file_exists($this->vfsRoot->url() . '/' . $file->getFileName());
    }

    public function createDirectory(string $directoryName): void
    {
        $fullPath = $this->vfsRoot->url() . '/' . $directoryName;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
    }
}
