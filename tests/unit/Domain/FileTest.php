<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

use function Safe\file_get_contents;

class FileTest extends TestCase
{
    public function testCreateFileFromAnExistingFile(): void
    {
        $content = file_get_contents(__DIR__ . "/resources/hello.txt");
        $file = new File(new FileName('hello.txt'), new Path(), new FileContent($content));
        $this->assertEquals('hello.txt', $file->getFileName());
        $this->assertEquals("Hello", $file->getContent());
    }

    public function testFileId(): void
    {
        $file = new File(new FileName("test"));
        $this->assertStringStartsWith("fil_", $file->getId());
    }

    public function testFileCreationDate(): void
    {
        $format = "Y/m/d H:m:i";
        $now = new DateTimeImmutable();
        $file = new File(new FileName("test"));
        $this->assertEquals($now->format($format), $file->getDate()->format($format));
    }
}
