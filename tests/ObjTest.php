<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Obj;
use Devly\Utils\Tests\Fake\Foo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class ObjTest extends TestCase
{
    public function testCreateReflectionFunction(): void
    {
        $rm = Obj::createReflection('preg_match');

        $this->assertInstanceOf(ReflectionFunction::class, $rm);
    }

    public function testCreateReflectionMethod(): void
    {
        $rm = Obj::createReflection([new Foo(), 'getBar']);

        $this->assertInstanceOf(ReflectionMethod::class, $rm);

        $rm = Obj::createReflection(Foo::class . '::getBar');

        $this->assertInstanceOf(ReflectionMethod::class, $rm);

        $rm = Obj::createReflection(Foo::class . '@getBar');

        $this->assertInstanceOf(ReflectionMethod::class, $rm);
    }

    public function testCreateReflectionClass(): void
    {
        $rc = Obj::createReflection(Foo::class);

        $this->assertInstanceOf(ReflectionClass::class, $rc);
    }

    public function testGetMethods(): void
    {
        $methods = Obj::getMethods(Foo::class);

        $this->assertInstanceOf(ReflectionMethod::class, $methods[0]);
    }
}
