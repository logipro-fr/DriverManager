<?php

namespace DriveManager\Tests\Infrastructure\Persistence\File;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\Persistence\File\Exceptions\FileNotFoundException;
use PHPUnit\Framework\TestCase;

abstract class FileRepositoryTestBase extends TestCase
{
    protected FileRepositoryInterface $fileRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->init(); // Assurez-vous que cette méthode est implémentée dans les classes dérivées
    }

    abstract protected function init(): void;

    public function testFindById(): void
    {
        $file = new File(new FileName("test"), new Path(), new FileContent("some content"), new FileId("unId"));
        $file2 = new File(new FileName("test2"), new Path(), new FileContent("some content"), new FileId("un2emeId"));

        $this->fileRepository->add($file);
        $found = $this->fileRepository->findById(new FileId("unId"));
        $this->fileRepository->add($file2);
        $found2 = $this->fileRepository->findById(new FileId("un2emeId"));

        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals("unId", $found->getId());
        $this->assertFalse($found->getId()->equals($found2->getId()));
    }

    public function testFindByIdException(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("Error can't find the fileId");
        $this->fileRepository->findById(new FileId("unknowId"));
    }
}
