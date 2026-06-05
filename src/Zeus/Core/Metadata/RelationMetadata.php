<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

/**
 * Class RelationMetadata
 *
 * Represents the metadata schema for a relationship between entities.
 * Uses PHP 8.4 asymmetric visibility to ensure immutability from the outside
 * once hydrated.
 */
readonly class RelationMetadata
{
    /**
     * @param int $id The unique database ID.
     * @param string $uuid The unique UUID.
     * @param int $source_entity_id The source entity database ID.
     * @param int $target_entity_id The target entity database ID.
     * @param string $relation_type The type of relationship (e.g., "one-to-many", "many-to-one").
     * @param string $source_field The field name acting as key on source.
     * @param string $target_field The field name acting as key on target.
     * @param int $version The optimistic locking version.
     */
    public function __construct(
        public int $id,
        public string $uuid,
        public int $source_entity_id,
        public int $target_entity_id,
        public string $relation_type,
        public string $source_field,
        public string $target_field,
        public int $version,
    ) {}
}
