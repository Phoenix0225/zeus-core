<?php

declare(strict_types=1);

namespace Zeus\Core\Rules;

readonly class RuleMetadata
{
    public function __construct(
        public string $trigger,
        public string $entityCode,
        public array $conditions,
        public array $actions
    ) {}
}
