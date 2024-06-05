<?php

namespace DriveManager\Domain\Model\File;

class FileContent
{
    public function __construct(private string $content = '')
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
