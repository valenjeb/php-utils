<?php

declare(strict_types=1);

namespace Devly\Utils\Tests;

use Devly\Utils\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testProcessPattern(): void
    {
        $this->assertEquals('/author/(?P<username>\w+)', Path::processPattern('/author/{username:w}'));
        $this->assertEquals('/author/(?P<username>[A-Za-z0-9]+)', Path::processPattern('/author/{username:alnum}'));
        $this->assertEquals('/author/(?P<username>[A-Za-z]+)', Path::processPattern('/author/{username:a}'));
        $this->assertEquals('/author/(?P<id>\d+)', Path::processPattern('/author/{id:d}'));
        $this->assertEquals('/author/(?P<username>[-\w]+)', Path::processPattern('/author/{username}'));
        $this->assertEquals(
            '/author/(?P<username>\@[a-zA-Z]+)',
            Path::processPattern('/author/{username:\@[a-zA-Z]+}')
        );
    }
}
