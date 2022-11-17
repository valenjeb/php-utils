<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Helpers;
use PHPUnit\Framework\TestCase;
use stdClass;

class HelpersTest extends TestCase
{
    public function testGetBoolType(): void
    {
        $this->assertEquals('bool', Helpers::getType(true));
    }

    public function testGetIntType(): void
    {
        $this->assertEquals('int', Helpers::getType(1));
    }

    public function testGetFloatType(): void
    {
        $this->assertEquals('double', Helpers::getType(1.1));
    }

    public function testGetStringType(): void
    {
        $this->assertEquals('string', Helpers::getType('foo'));
    }

    public function testGetNullType(): void
    {
        $this->assertEquals('null', Helpers::getType(null));
    }

    public function testGetObjectType(): void
    {
        $this->assertEquals('stdClass', Helpers::getType(new stdClass()));
    }

    public function testGetCallableType(): void
    {
        $this->assertEquals('callable', Helpers::getType('preg_match'));
    }

    public function testGetCallableArrayType(): void
    {
        $this->assertEquals('callable', Helpers::getType([$this, __METHOD__]));
    }

    public function testGetArrayType(): void
    {
        $this->assertEquals('array', Helpers::getType([]));
    }
}
