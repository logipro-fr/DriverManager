<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\Exceptions\RepositoryDoesNotExistException;
use DriveManager\Domain\Model\File\File;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

class DropFileForFileSystem implements DropFileInterface
{
    private const FIND_FILE_OR_DIRECTORY = "%s%s%s";
    private vfsStreamDirectory $root;

    public function __construct()
    {
        $this->root = vfsStream::setup("root");
    }

    public function dropFile(File $file): void
    {
        $pathToUpload = $file->getPath();
        if (!$this->root->hasChild($pathToUpload)) {
            throw new RepositoryDoesNotExistException("Repository ($pathToUpload) doesn't exist.");
        }
        $this->root->addChild(vfsStream::newFile($file->getFileName())
                    ->setContent($file->getContent())
                    ->at($this->root));
    }

    public function isFileExists(File $file): bool
    {
        return file_exists(sprintf(self::FIND_FILE_OR_DIRECTORY, $this->root->url(), '/', $file->getFileName()));
    }

    public function createDirectory(string $directoryName): void
    {
        $fullPath = sprintf(self::FIND_FILE_OR_DIRECTORY, $this->root->url(), '/', $directoryName);
        if (!is_dir($fullPath)) {
            vfsStream::newDirectory($directoryName)->at($this->root);
        }
    }

    public function getRootDirectory(): vfsStreamDirectory
    {
        return $this->root;
    }
}
