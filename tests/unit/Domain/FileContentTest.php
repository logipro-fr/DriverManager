<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Domain\Model\File\FileContent;
use PHPUnit\Framework\TestCase;

class FileContentTest extends TestCase
{
    public function testCreateContent(): void
    {
        $content = new FileContent("contenu de mon fichier");
        $this->assertEquals("contenu de mon fichier", $content->getContent());
    }

    public function testCreateEmptyContent(): void
    {
        $content = new FileContent();
        $this->assertEquals('', $content->getContent());
    }
}
