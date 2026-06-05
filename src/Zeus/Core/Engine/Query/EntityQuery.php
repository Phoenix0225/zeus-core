<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Query;

use Zeus\Core\Metadata\EntityMetadata;

/**
 * Class EntityQuery
 *
 * Represents the final, validated state of an agnostic query against a specific entity.
 */
class EntityQuery
{
    /**
     * @var array<string>
     */
    public private(set) array $selectedFields = [];

    /**
     * @var array<QueryCriteria>
     */
    public private(set) array $criteria = [];

    /**
     * @var array<string>
     */
    public private(set) array $relationsToLoad = [];

    public function __construct(
        public private(set) EntityMetadata $entity,
    ) {}

    public function addSelectedField(string $field): void
    {
        $this->selectedFields[] = $field;
    }

    public function addCriteria(QueryCriteria $criteria): void
    {
        $this->criteria[] = $criteria;
    }

    public function addRelation(string $relationCode): void
    {
        $this->relationsToLoad[] = $relationCode;
    }
}
