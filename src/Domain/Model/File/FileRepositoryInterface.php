<?php

namespace DriveManager\Domain\Model\File;

interface FileRepositoryInterface
{
    public function add(File $file): void;

    public function findById(FileId $id): File;
}
