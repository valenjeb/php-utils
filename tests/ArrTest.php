<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Arr;
use PHPUnit\Framework\TestCase;
use stdClass;

use function is_int;

class ArrTest extends TestCase
{
    public function testRenameKey(): void
    {
        $arr = ['foo' => 'bar'];

        Arr::renameKey($arr, 'foo', 'baz');

        $this->assertEquals(['baz' => 'bar'], $arr);
    }

    public function testEach(): void
    {
        $arr = [1, 2];

        Arr::each($arr, function ($item): void {
            $this->assertContains($item, [1, 2]);
        });
    }

    public function testInvoke(): void
    {
        $callbacks = [
            '+' => static function ($a, $b) {
                return $a + $b;
            },
            '*' => static function ($a, $b) {
                return $a * $b;
            },
        ];

        $results = Arr::invoke($callbacks, 5, 11);

        $this->assertEquals(['+' => 16, '*' => 55], $results);
    }

    public function testInvokeMethod(): void
    {
        $obj1 = new class {
            public function foo(int $a, int $b): int
            {
                return $a + $b;
            }
        };
        $obj2 = new class {
            public function foo(int $a, int $b): int
            {
                return $a - $b;
            }
        };

        $objects = ['a' => $obj1, 'b' => $obj2];

        $array = Arr::invokeMethod($objects, 'foo', 1, 2);

        $this->assertEquals(['a' => 3, 'b' => -1], $array);
    }

    public function testToObject(): void
    {
        $arr = ['foo' => 'bar'];

        $obj = new stdClass();
        $obj = Arr::toObject($arr, $obj);

        $this->assertEquals('bar', $obj->foo);
    }

    public function testSome(): void
    {
        $this->assertTrue(Arr::some(['foo', 'bar', 3], static fn ($value) => is_int($value)));
        $this->assertFalse(Arr::some(['foo', 'bar'], static fn ($value) => is_int($value)));
    }

    public function testFlatten(): void
    {
        $this->assertEquals([1, 2, 3, 4, 5, 6], Arr::flatten([1, 2, [3, 4, [5, 6]]]));
    }

    public function testDot(): void
    {
        $arr = [
            'product' => [
                'name' => 'Desk',
                'price' => 100,
            ],
        ];

        $this->assertEquals(['product.name' => 'Desk', 'product.price' => 100], Arr::dot($arr));
    }

    public function testAdd(): void
    {
        $array = Arr::add(['name' => 'Desk'], 'price', 100);

        $this->assertEquals(['name' => 'Desk', 'price' => 100], $array, 'Failed to add new item to the array.');

        $array = Arr::add(['name' => 'Desk', 'price' => null], 'price', 100);

        $this->assertEquals(['name' => 'Desk', 'price' => 100], $array, 'Failed to overwrite null value');

        $array = Arr::add(['name' => 'Desk', 'price' => 50], 'price', 100);

        $this->assertEquals(['name' => 'Desk', 'price' => 50], $array, 'Existing item should not be overwritten');
    }

    public function testSort(): void
    {
        $array = ['Desk', 'Table', 'Chair'];

        $sorted = Arr::sort($array);

        $this->assertEquals(['Chair', 'Desk', 'Table'], $sorted);

        $array = [
            ['name' => 'Desk'],
            ['name' => 'Table'],
            ['name' => 'Chair'],
        ];
    }

    public function testSortRecursive(): void
    {
        $array = [
            ['Roman', 'Taylor', 'Li'],
            ['PHP', 'Ruby', 'JavaScript'],
            ['one' => 1, 'two' => 2, 'three' => 3],
        ];

        $sorted = Arr::sortRecursive($array);

        $this->assertEquals([
            ['JavaScript', 'PHP', 'Ruby'],
            ['one' => 1, 'three' => 3, 'two' => 2],
            ['Li', 'Roman', 'Taylor'],
        ], $sorted);
    }

    public function testGetKeyOffset(): void
    {
        $array = [
            'name' => 'John',
            'age'  => 35,
        ];

        $this->assertEquals(1, Arr::getKeyOffset($array, 'age'));
    }

    public function testFirst(): void
    {
        $array = [1, 2, 3];

        $this->assertEquals(1, Arr::first($array));
    }

    public function testFirstPass(): void
    {
        $array = [100, 200, 300];

        $this->assertEquals(200, Arr::firstPass($array, static fn ($item) => $item >= 150));
    }

    public function testLast(): void
    {
        $array = [1, 2, 3];

        $this->assertEquals(3, Arr::last($array));
    }

    public function testPick(): void
    {
        $array = ['name' => 'Desk', 'price' => 100];

        $res = Arr::pick($array, 'price');

        $this->assertEquals(100, $res);
        $this->assertEquals(['name' => 'Desk'], $array);
    }

    public function testMap(): void
    {
        $array = Arr::map([1, 2, 3], static fn ($item, $ddd) => $item + 1);

        $this->assertEquals([2, 3, 4], $array);
    }

    public function testMergeTree(): void
    {
        $array1 = ['color' => ['favorite' => 'red'], 5];
        $array2 = [10, 'color' => ['favorite' => 'green', 'blue']];

        $array = Arr::mergeTree($array1, $array2);
        $this->assertEquals(['color' => ['favorite' => 'red', 'blue'], 5], $array);
    }

    public function testGetItemUsingDotNotation(): void
    {
        $array = ['products' => ['desk' => ['price' => 100]]];

        $price = Arr::get($array, 'products.desk.price');

        $this->assertEquals(100, $price);
    }

    public function testEvery(): void
    {
        $this->assertTrue(Arr::every([1, 2, 3], static fn ($value) => is_int($value)));
        $this->assertFalse(Arr::every([1, 2, '3'], static fn ($value) => is_int($value)));
    }
}
