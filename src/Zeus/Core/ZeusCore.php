<?php

declare(strict_types=1);

namespace Zeus\Core;

final class ZeusCore
{
    public const VERSION = '0.1.0';

    public static function version(): string
    {
        return self::VERSION;
    }
}