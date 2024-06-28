<?php

namespace DriveManager\Tests\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Infrastructure\Persistence\Doctrine\Types\FileNameType;
use PHPUnit\Framework\TestCase;

class FileNameTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('file_name', (new FileNameType())->getName());
    }

    public function testConvertValue(): void
    {
        $type = new FileNameType();
        $dbValue = $type->convertToDatabaseValue(
            $fileName = new FileName("TestFile.txt"),
            new SqlitePlatform()
        );

        $this->assertIsString($dbValue);
        $phpValue = $type->convertToPHPValue($dbValue, new SqlitePlatform());
        $this->assertEquals($fileName, $phpValue);
    }
}
