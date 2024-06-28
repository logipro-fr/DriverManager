<?php

namespace DriveManager\Tests\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Infrastructure\Persistence\Doctrine\Types\FileContentType;
use PHPUnit\Framework\TestCase;

class FileContentTypeTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals('file_content', (new FileContentType())->getName());
    }

    public function testConvertValue(): void
    {
        $type = new FileContentType();
        $dbValue = $type->convertToDatabaseValue(
            $fileContent = new FileContent(),
            new SqlitePlatform()
        );

        $this->assertIsString($dbValue);
        $phpValue = $type->convertToPHPValue($dbValue, new SqlitePlatform());
        $this->assertEquals($fileContent, $phpValue);
    }

    public function testGetSQLDeclaration(): void
    {
        $type = new FileContentType();
        $platform = new SqlitePlatform();
        $column = ['name' => 'file_content_column'];

        $sqlDeclaration = $type->getSQLDeclaration($column, $platform);
        $this->assertEquals($platform->getGuidTypeDeclarationSQL($column), $sqlDeclaration);
    }
}
