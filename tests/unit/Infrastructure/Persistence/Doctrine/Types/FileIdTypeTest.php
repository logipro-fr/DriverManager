<?php

namespace DriveManager\Tests\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Infrastructure\Persistence\Doctrine\Types\FileIdType;
use PHPUnit\Framework\TestCase;

class FileIdTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('file_id', (new FileIdType())->getName());
    }

    public function testConvertValue(): void
    {
        $type = new FileIdType();
        $dbValue = $type->convertToDatabaseValue(
            $fileId = new FileId(),
            new SqlitePlatform()
        );

        $this->assertIsString($dbValue);
        $phpValue = $type->convertToPHPValue($dbValue, new SqlitePlatform());
        $this->assertEquals($fileId, $phpValue);
    }
}
