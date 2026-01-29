<?php

namespace Zeus\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zeus\Core\ZeusCore;

class CoreTest extends TestCase
{
    public function test_it_exposes_a_version(): void
    {
        $this->assertSame('0.1.0', ZeusCore::version());
    }
}