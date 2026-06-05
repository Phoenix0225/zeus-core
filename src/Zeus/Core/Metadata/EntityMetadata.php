<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

/**
 * Class EntityMetadata
 *
 * Represents the metadata schema for a core ERP entity.
 * Uses PHP 8.4 asymmetric visibility to ensure immutability from the outside
 * once hydrated.
 */
class EntityMetadata
{
    /**
     * @param int $id The unique database ID.
     * @param string $uuid The unique UUID.
     * @param string $code The unique logical entity code.
     * @param string $name The human-readable name.
     * @param string|null $description An optional description of the entity.
     * @param string $module_code The code of the module owning this entity.
     * @param bool $is_active Whether this entity is currently active.
     * @param int $version The optimistic locking version.
     */
    public function __construct(
        public private(set) int $id,
        public private(set) string $uuid,
        public private(set) string $code,
        public private(set) string $name,
        public private(set) ?string $description,
        public private(set) string $module_code,
        public private(set) bool $is_active,
        public private(set) int $version,
    ) {}
}
