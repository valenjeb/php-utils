<?php

declare(strict_types=1);

namespace Devly\Utils;

use Exception;

trait StaticClass
{
    /** @throws Exception */
    final public function __construct()
    {
        throw new Exception('Class ' . static::class . ' is static and cannot be instantiated.');
    }
}
