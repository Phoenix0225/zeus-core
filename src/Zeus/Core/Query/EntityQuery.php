<?php

declare(strict_types=1);

namespace Zeus\Core\Query;

use Zeus\Core\Metadata\EntityMetadata;

class EntityQuery
{
    private array $conditions = [];

    public function __construct(
        private readonly EntityMetadata $entity
    ) {}

    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function getEntity(): EntityMetadata
    {
        return $this->entity;
    }

    /**
     * @return Condition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }
}
