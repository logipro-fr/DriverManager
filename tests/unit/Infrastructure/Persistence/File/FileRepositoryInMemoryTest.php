<?php

namespace DriveManager\Tests\Infrastructure\Persistence\Post;

use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use DriveManager\Tests\Infrastructure\Persistence\File\FileRepositoryTestBase;

class FileRepositoryInMemoryTest extends FileRepositoryTestBase
{
    protected function init(): void
    {
        $this->fileRepository = new FileRepositoryInMemory();
    }
}
