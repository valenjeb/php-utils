<?php

declare(strict_types=1);

namespace Devly\Utils\Tests\Fake;

use Devly\Utils\SmartObject;

/**
 * @property-read string $baz
 * @property string $bar
 * @method string get_bar()
 * @method void set_bar(string $text)
 */
class Foo
{
    use SmartObject;
    use FooTrait;

    private string $bar = 'baz';

    public function getBar(): string
    {
        return $this->bar;
    }

    public function setBar(string $text): void
    {
        $this->bar = $text;
    }

    public function getBaz(): string
    {
        return 'baz';
    }
}
