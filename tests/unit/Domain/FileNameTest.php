<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Domain\Model\File\FileName;
use PHPUnit\Framework\TestCase;

class FileNameTest extends TestCase
{
    public function testCreateFileName(): void
    {
        $content = new FileName("Nom_fichier.txt");
        $this->assertEquals("Nom_fichier.txt", $content->getFileName());
    }
}
