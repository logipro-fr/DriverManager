<?php

namespace DriveManager\Domain\Model\File;

use Safe\DateTimeImmutable;

class File
{
    private DateTimeImmutable $date;

    public function __construct(
        private FileName $fileName,
        private Path $path = new Path(),
        private FileContent $content = new FileContent(),
        private FileId $id = new FileId()
    ) {
        $this->date = new DateTimeImmutable();
    }

    public function getFileName(): string
    {
        return $this->fileName->getFileName();
    }

    public function getContent(): string
    {
        return $this->content->getContent();
    }

    public function getId(): FileId
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getPath(): string
    {
        return $this->path; //->getPath().$this->fileName
    }
}
