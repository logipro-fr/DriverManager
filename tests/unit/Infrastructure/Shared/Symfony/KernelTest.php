<?php

namespace DriveManager\Tests\Infrastructure\Shared\Symfony;

use DriveManager\Infrastructure\Shared\Symfony\Kernel;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    public function testConstruct(): void
    {

        $kernel = new Kernel("test", true);
        $this->assertInstanceOf(Kernel::class, $kernel);
        $this->assertTrue($kernel->isDebug());
    }
}
