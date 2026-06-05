<?php

declare(strict_types=1);

namespace Zeus\Core\UI;

readonly class MenuNode
{
    public function __construct(
        public string $id,
        public string $label,
        public string $icon,
        public ?string $screenId = null,
        public array $children = [],
    ) {
    }
}
