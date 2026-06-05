<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

use Zeus\Core\Metadata\Enums\RelationType;

readonly class RelationMetadata
{
    public function __construct(
        public string $name,
        public RelationType $type,
        public string $targetEntityCode,
        public string $localKey,
        public string $foreignKey
    ) {}
}
