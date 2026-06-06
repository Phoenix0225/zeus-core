<?php

declare(strict_types=1);

namespace Zeus\Core\Rules;

interface ActionResolverInterface
{
    public function resolve(string $actionClass): ActionInterface;
}
