<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Tests\Fake\StaticClassObject;
use Exception;
use PHPUnit\Framework\TestCase;

class StaticClassTest extends TestCase
{
    public function testInitiationThrowsException(): void
    {
        $this->expectException(Exception::class);

        new StaticClassObject();
    }
}
