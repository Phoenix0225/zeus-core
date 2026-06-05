<?php

declare(strict_types=1);

namespace Zeus\Core\UI;

readonly class ScreenMetadata
{
    public function __construct(
        public string $id,
        public string $type,
        public ?string $entityCode = null,
        public array $config = [],
    ) {
    }
}
