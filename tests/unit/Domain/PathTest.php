<?php

namespace DriveManager\Tests\Domain;

use DriveManager\Application\Service\DropFile\Exceptions\IncompletePathException;
use DriveManager\Domain\Model\File\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testPathWithLinuxStyle(): void
    {
        $path = new Path("/home/dev");
        $this->assertEquals("/home/dev", $path->getPath());
    }

    public function testPathWithNextCloudStyle(): void
    {
        $url = "/url/test/ApiNextcloud.txt";
        $path = new Path($url);
        $this->assertEquals($url, $path->getPath());
    }

    public function testDetectIncompleteNextCloudPathException(): void
    {
        $path = 'com/owncloud/remote.php/dav/files/romain.malosse@logipro.com/Test';
        $this->expectException(IncompletePathException::class);
        $this->expectExceptionMessage("NextCloud drive detect but seem incomplete. Please check path '$path'");

        new Path("$path");
    }

    public function testCorrectFileSystemButWithStrangeWord(): void
    {
        $path = new Path("http://owncloud/nextcloud");
        $this->assertEquals("http://owncloud/nextcloud", $path->getPath());
    }
}
