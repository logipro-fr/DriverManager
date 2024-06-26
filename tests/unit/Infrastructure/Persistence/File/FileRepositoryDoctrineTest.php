<?php

namespace DriveManager\Tests\Infrastructure\File\Persistence\File;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryDoctrine;
use DriveManager\Tests\Infrastructure\Persistence\File\FileRepositoryTestBase;

class FileRepositoryDoctrineTest extends FileRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(['posts']);
        $this->fileRepository = new FileRepositoryDoctrine($this->getEntityManager());
    }

    protected function init(): void
    {
    }

    public function testFlush(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->name =  FileRepositoryDoctrineTest::class;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('flush');

        $entityManager
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $sut = new FileRepositoryDoctrine($entityManager);
        $sut->flush();
    }
}
