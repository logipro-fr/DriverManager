<?php

namespace DriveManager\Infrastructure\Persistence\File;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\Persistence\File\Exceptions\FileNotFoundException;

/**
 * @extends EntityRepository<File>
 */
class FileRepositoryDoctrine extends EntityRepository implements FileRepositoryInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = $em->getClassMetadata(File::class);
        parent::__construct($em, $class);
    }

    public function add(File $file): void
    {
        $this->getEntityManager()->persist($file);
    }

    public function findById(FileId $searchId): File
    {
        $file = $this->getEntityManager()->find(File::class, $searchId);
        if ($file === null) {
            throw new FileNotFoundException(sprintf("Error can't find the fileId %s", $searchId), 400);
        }
        return $file;
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
