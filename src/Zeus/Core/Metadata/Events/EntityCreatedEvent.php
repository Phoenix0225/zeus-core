<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata\Events;

use Zeus\Core\Metadata\EntityMetadata;

readonly class EntityCreatedEvent
{
    public function __construct(
        public EntityMetadata $entity,
    ) {
    }
}
