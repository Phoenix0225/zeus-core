<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata\Events;

use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;

readonly class FieldDeletedEvent
{
    public function __construct(
        public EntityMetadata $entity,
        public FieldMetadata $field,
    ) {
    }
}
