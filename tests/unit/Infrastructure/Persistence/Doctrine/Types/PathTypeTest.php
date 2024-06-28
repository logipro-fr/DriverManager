<?php

namespace DriveManager\Tests\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\Persistence\Doctrine\Types\PathType;
use PHPUnit\Framework\TestCase;

class PathTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('path', (new PathType())->getName());
    }

    public function testConvertValue(): void
    {
        $type = new PathType();
        $dbValue = $type->convertToDatabaseValue(
            $path = new Path(),
            new SqlitePlatform()
        );

        $this->assertIsString($dbValue);
        $phpValue = $type->convertToPHPValue($dbValue, new SqlitePlatform());
        $this->assertEquals($path, $phpValue);
    }
}
