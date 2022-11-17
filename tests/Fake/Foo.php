<?php

declare(strict_types=1);

namespace Devly\Utils\Tests\Fake;

class Foo
{
    use FooTrait;

    public function bar(): string
    {
        return 'bar';
    }
}
