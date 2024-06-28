<?php

namespace DriveManager\Domain\Model\File;

class FileId
{
    public function __construct(private string $id = "")
    {
        if (empty($id)) {
            $this->id =  uniqid("fil_");
        }
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(FileId $fileId): bool
    {
        if ($this->id === $fileId->id) {
            return true;
        }
        return false;
    }
}
