<?php

namespace DriveManager\Tests\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\Exceptions\RepositoryDoesNotExistException;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

use function Safe\fileperms;

class DropFileForFileSystemTest extends TestCase
{
    public function testDropOneFile(): void
    {
        $dropFile = new DropFileForFileSystem();
        $file = new File(new FileName('myHello.txt'), new Path("testingRepo"), new FileContent('Hello, World!'));
        $dropFile->createDirectory("testingRepo");
        $dropFile->dropFile($file);
        //$dropFile->getRootDirectory()->addChild(vfsStream::newFile('myHello.txt'));

        //var_dump($this->root);
        $this->assertTrue($dropFile->getRootDirectory()->hasChild('myHello.txt'));
        //$this->assertTrue($dropFile->isFileExists($file));
        $this->assertEquals("Hello, World!", $file->getContent());
    }

    public function testFileDoesntExiste(): void
    {
        $dropFile = new DropFileForFileSystem();
        $file = new File(new FileName('unxistingFile.txt'), new Path("root/unxistingFile.txt"), new FileContent(''));

        $this->assertFalse($dropFile->getRootDirectory()->hasChild('unxistingFile.txt'));
        $this->assertFalse($dropFile->isFileExists($file));
    }

    public function testThrowExceptionRepositoryDoesntExiste(): void
    {
        $this->expectException(RepositoryDoesNotExistException::class);
        $this->expectExceptionMessageMatches("/Repository (.*) doesn't exist./");

        $dropFile = new DropFileForFileSystem();
        $file = new File(new FileName('noneExistingFile.txt'), new Path('nonexistentDir/'));

        $dropFile->dropFile($file);
    }


    public function testCreateDirectory(): void
    {
        $dropFile = new DropFileForFileSystem();
        $newDirectory = 'testDirectory';

        $dropFile->createDirectory($newDirectory);

        // Assert the directory was created
        $this->assertTrue(is_dir(vfsStream::url('root/' . $newDirectory)));
    }

    public function testCreateDirectoryRecursively(): void
    {
        $dropFile = new DropFileForFileSystem();
        $deepDirectory = 'Directory1/Directory2/Directory3';

        $dropFile->createDirectory($deepDirectory);

        $this->assertTrue(is_dir(vfsStream::url('root/' . $deepDirectory)));
    }

    public function testCreateDirectoryWithCorrectPermissions(): void
    {
        $dropFile = new DropFileForFileSystem();
        $newDirectory = 'testDirectoryPermission';
        $dropFile->createDirectory($newDirectory);
        $permissions = fileperms(vfsStream::url('root/' . $newDirectory)) & 0777;

        $this->assertEquals(0777, $permissions);
    }
}
