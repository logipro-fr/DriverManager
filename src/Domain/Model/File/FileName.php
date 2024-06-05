<?php

namespace DriveManager\Domain\Model\File;

class FileName
{
    public function __construct(private string $fileName)
    {
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
