<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

/**
 * Class BusinessKeyMetadata
 *
 * Represents the metadata schema for a business key defined on an entity.
 * Uses PHP 8.4 asymmetric visibility to ensure immutability from the outside
 * once hydrated.
 */
class BusinessKeyMetadata
{
    /**
     * @param int $id The unique database ID.
     * @param string $uuid The unique UUID.
     * @param int $entity_id The entity to which this business key belongs.
     * @param string $name The logical name of the business key.
     * @param bool $is_primary Whether this business key acts as the primary logical identifier.
     * @param int $version The optimistic locking version.
     */
    public function __construct(
        public private(set) int $id,
        public private(set) string $uuid,
        public private(set) int $entity_id,
        public private(set) string $name,
        public private(set) bool $is_primary,
        public private(set) int $version,
    ) {}
}
