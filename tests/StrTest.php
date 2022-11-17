<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testEndsWith(): void
    {
        $this->assertTrue(Str::endsWith('This is my name', 'name'));
        $this->assertTrue(Str::endsWith('This is my name', ['foo', 'name']));
    }

    public function testStartsWith(): void
    {
        $this->assertTrue(Str::startsWith('This is my name', 'This'));
        $this->assertTrue(Str::startsWith('This is my name', ['name', 'This']));
    }

    public function testSnake(): void
    {
        $this->assertEquals('foo_bar', Str::snake('fooBar'));
        $this->assertEquals('foo-bar', Str::snake('fooBar', '-'));
    }

    public function testUpper(): void
    {
        $this->assertEquals('FOO', Str::upper('foo'));
    }

    public function testWordCount(): void
    {
        $this->assertEquals(2, Str::wordCount('Hello, world!'));
    }

    public function testUcfirst(): void
    {
        $this->assertEquals('Foo', Str::ucfirst('foo'));
    }

    public function testKebab(): void
    {
        $this->assertEquals('foo-bar', Str::kebab('fooBar'));
        $this->assertEquals('foo-bar', Str::kebab('FooBar'));
    }

    public function testLength(): void
    {
        $this->assertEquals(5, Str::length('Devly'));
    }

    public function testCamelize(): void
    {
        $this->assertEquals('fooBar', Str::camelize('foo_bar'));
        $this->assertEquals('fooBar', Str::camelize('foo-bar'));
        $this->assertEquals('fooBar', Str::camelize('foo bar'));
    }

    public function testAscii(): void
    {
        $this->assertEquals('u', Str::ascii('û'));
    }

    public function testCapitalize(): void
    {
        $this->assertEquals('Foo Bar', Str::capitalize('foo bar'));
    }

    public function testSubstr(): void
    {
        $this->assertEquals('ipsum', Str::substr('Lorem ipsum dolor', 6, 5));
    }

    public function testContains(): void
    {
        $this->assertTrue(Str::contains('Lorem ipsum dolor', 'ipsum'));
    }

    public function testContainsWithArray(): void
    {
        $this->assertTrue(Str::contains('Lorem ipsum dolor', ['ipsum', 'dolor']));
        $this->assertFalse(Str::contains('Lorem ipsum dolor', ['ipsum', 'foo']));
    }

    public function testContainsNotStrict(): void
    {
        $this->assertTrue(Str::contains('Lorem ipsum dolor', ['ipsum', 'foo'], false));
    }

    public function testClassify(): void
    {
        $this->assertEquals('FooBar', Str::classify('foo-bar'));
        $this->assertEquals('FooBar', Str::classify('foo_bar'));
        $this->assertEquals('FooBar', Str::classify('foo bar'));
        $this->assertEquals('FooBar', Str::classify('fooBar'));
    }

    public function testReplace(): void
    {
        $string = 'http://example.com';

        $this->assertEquals('https://example.com', Str::replace('http', 'https', $string));
    }

    public function testLcfirst(): void
    {
        $this->assertEquals('foo Bar', Str::lcfirst('Foo Bar'));
    }

    public function testLower(): void
    {
        $this->assertEquals('devly', Str::lower('DEVLY'));
    }

    public function testMatch(): void
    {
        $this->assertEquals('bar', Str::match('/bar/', 'foo bar'));
        $this->assertEquals('bar', Str::match('/foo (.*)/', 'foo bar'));
        $this->assertNull(Str::match('/moo/', 'foo bar'));
    }

    public function testMatchAll(): void
    {
        $this->assertEquals(['bar', 'bar'], Str::match('/bar/', 'bar foo bar', true));
        $this->assertEquals(null, Str::match('/moo/', 'bar foo bar', true));
    }

    public function testSplit(): void
    {
        $this->assertEquals(['foo', 'bar'], Str::split('foo bar', '/\s/'));
    }
}
