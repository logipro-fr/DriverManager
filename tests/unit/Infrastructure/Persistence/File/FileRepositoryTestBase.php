<?php

namespace DriveManager\Tests\Infrastructure\Persistence\File;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;

abstract class FileRepositoryTestBase extends TestCase
{
    protected FileRepositoryInterface $fileRepository;

    protected function setUp(): void
    {
        $this->init();
    }

    abstract protected function init(): void;
    
    public function testFindById(): void
    {
        $file = new File(new FileName("test"), new Path(), new FileContent(), new FileId("unId"));
        $this->fileRepository->add($file);
        $found = $this->fileRepository->findById(new FileId("unId"));
        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals(new FileId("unId"), $found->getId());
    }
}
