<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Resolver;

use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;

/**
 * Class ResolvedRelation
 *
 * Immutable DTO representing a fully resolved metadata relationship between two entities.
 */
readonly class ResolvedRelation
{
    /**
     * ResolvedRelation constructor.
     *
     * @param EntityMetadata $sourceEntity The source entity of the relationship.
     * @param EntityMetadata $targetEntity The target entity of the relationship.
     * @param FieldMetadata $sourceField The field acting as a key on the source entity.
     * @param FieldMetadata $targetField The field acting as a key on the target entity.
     * @param string $relationType The type of relationship (e.g. 'one-to-many', 'many-to-one').
     */
    public function __construct(
        public EntityMetadata $sourceEntity,
        public EntityMetadata $targetEntity,
        public FieldMetadata $sourceField,
        public FieldMetadata $targetField,
        public string $relationType
    ) {}
}
