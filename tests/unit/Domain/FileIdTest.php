<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Domain\Model\File\FileId;
use PHPUnit\Framework\TestCase;

class FileIdTest extends TestCase
{
    public function testIdentify(): void
    {
        $id1 = new FileId();
        $id2 = new FIleId();
        $this->assertFalse($id1->equals($id2));
    }

    public function testIdentify2(): void
    {
        $id1 = new FileId();
        $this->assertTrue($id1->equals($id1));
    }

    public function testValueId(): void
    {
        $id = new FileId("fil_id");
        $this->assertEquals("fil_id", $id);
    }
}
