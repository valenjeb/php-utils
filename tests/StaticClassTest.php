<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Tests\Fake\StaticFoo;
use Error;
use PHPUnit\Framework\TestCase;

class StaticClassTest extends TestCase
{
    public function testInitiationThrowsException(): void
    {
        $this->expectException(Error::class);

        new StaticFoo();
    }
}
