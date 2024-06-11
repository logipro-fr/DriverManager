<?php

namespace DriveManager\Infrastructure\Persistence\File;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileRepositoryInterface;

class FileRepositoryInMemory implements FileRepositoryInterface
{
    /**
     * @var array<string, File> $files
     */
    private array $files;

    public function add(File $file): void
    {
        $this->files[$file->getid()->__toString()] = $file;
    }

    public function findById(FileId $id): File
    {
        return $this->files[$id->__toString()];
    }
}
