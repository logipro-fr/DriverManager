<?php

namespace DriveManager\Application\Service\DropFile;

use DriveManager\Domain\Model\File\File;

interface DropFileInterface
{
    public function dropFile(File $file): void;
    public function isFileExists(File $file): bool;
}
