<?php

namespace DriveManager\Tests\Infrastructure\Persistence;

use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\Persistence\FileRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class FileRepositoryInMemoryTest extends TestCase
{
    public function testFindById(): void
    {
        $file = new File(new FileName("test"), new Path(), new FileContent(), new FileId("unId"));
        $repository = new FileRepositoryInMemory();
        $repository -> add($file);
        $found = $repository->findById(new FileId("unId"));
        $this->assertInstanceOf(File::class, $found);
        $this->assertEquals(new FileId("unId"), $found->getId());
    }
}
