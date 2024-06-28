<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Domain\Model\File\FileContent;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;
use function Safe\getcwd;

class FileContentTest extends TestCase
{
    public function testCreateContent(): void
    {
        $content = new FileContent("contenu de mon fichier");
        $this->assertEquals("contenu de mon fichier", $content->getContent());
    }

    public function testReadBinaryContent(): void
    {
        $content = file_get_contents(getcwd() . "/tests/unit/Domain/resources/smallBinaryImage.jpg");
        $fileContent = new FileContent($content);
        $this->assertEquals($content, $fileContent->getContent());
    }

    public function testCreateEmptyContent(): void
    {
        $content = new FileContent();
        $this->assertEquals('', $content->getContent());
    }
}
