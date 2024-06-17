<?php

namespace DriveManager\Tests\Infrastructure\File\Persistence\File;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryDoctrine;
use DriveManager\Tests\Infrastructure\Persistence\File\FileRepositoryTestBase;

class FileRepositoryDoctrineTest extends FileRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function init(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(['files']);
        $this->fileRepository = new FileRepositoryDoctrine($this->getEntityManager());
    }

    public function testFlush(): void
    {
        $this->initDoctrineTester();
        $fileRepository = new FileRepositoryDoctrine($this->getEntityManager());
        $fileRepository->flush();
        $this->assertTrue(true);
    }
}

