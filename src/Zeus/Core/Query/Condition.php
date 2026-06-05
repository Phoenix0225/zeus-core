<?php

declare(strict_types=1);

namespace Zeus\Core\Query;

readonly class Condition
{
    public function __construct(
        public string $field,
        public string $operator,
        public mixed $value,
        public bool $allowNull = false
    ) {}
}
