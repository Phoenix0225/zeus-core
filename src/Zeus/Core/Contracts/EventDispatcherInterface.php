<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
