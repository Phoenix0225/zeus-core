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
class RelationMetadata
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
        public private(set) int $id,
        public private(set) string $uuid,
        public private(set) int $source_entity_id,
        public private(set) int $target_entity_id,
        public private(set) string $relation_type,
        public private(set) string $source_field,
        public private(set) string $target_field,
        public private(set) int $version,
    ) {}
}
