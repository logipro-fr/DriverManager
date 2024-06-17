<?php

namespace DriveManager\Infrastructure\Persistence\File;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\Persistence\File\Exceptions\FileNotFoundException;

class FileRepositoryInMemory implements FileRepositoryInterface
{
    /**
     * @var array<string, File> $files
     */
    private array $files = [];

    public function add(File $file): void
    {
        $this->files[$file->getid()->__toString()] = $file;
    }

    public function findById(FileId $id): File
    {
        if (!isset($this->files[$id->__toString()])) {
            throw new FileNotFoundException(sprintf("Error can't find the fileId %s", $id), 400);
        }
        return $this->files[$id->__toString()];
    }
}
