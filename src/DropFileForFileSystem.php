<?php

namespace DriveManager;

class DropFileForFileSystem implements DropFileInterface
{
    public function __construct(private string $rootPath)
    {
    }

    public function drop(string $fileName, string $fileContent): void
    {
        file_put_contents($this->rootPath . '/' . $fileName, $fileContent);
    }

    public function isFileExists(string $filename): bool
    {
        return file_exists($this->rootPath . '/' . $filename);
    }
}
