<?php

declare(strict_types=1);

namespace Devly\Utils;

use Error;

trait StaticClass
{
    /** @throws Error */
    final public function __construct()
    {
        throw new Error('Class ' . static::class . ' is static and cannot be instantiated.');
    }
}
