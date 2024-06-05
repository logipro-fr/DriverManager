<?php

namespace DriveManager\Tests;

use PHPUnit\Framework\TestCase;
use DriveManager\DropFileForFileSystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

use function Safe\file_get_contents;

class DropFileTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('root');
    }

    public function testDropOneFile(): void
    {
        $fileContent = file_get_contents(__DIR__ . '/ressources/hello.txt');

        $root = $this->root->url();
        $dropFile = new DropFileForFileSystem($root);
        $dropFile->drop('myHello.txt', $fileContent);

        $this->assertTrue($this->root->hasChild('myHello.txt'));
        $this->assertTrue($dropFile->isFileExists('myHello.txt'));
    }
}
