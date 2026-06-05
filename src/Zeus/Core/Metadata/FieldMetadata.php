<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

/**
 * Class FieldMetadata
 *
 * Represents the metadata schema for an entity field.
 * Uses PHP 8.4 asymmetric visibility to ensure immutability from the outside
 * once hydrated.
 */
class FieldMetadata
{
    /**
     * @param int $id The unique database ID.
     * @param string $uuid The unique UUID.
     * @param int $entity_id The owner entity ID.
     * @param string $table_name The database table name.
     * @param string $column_name The database column name.
     * @param string $label The human-readable label.
     * @param string $data_type The logical/business data type of the field.
     * @param int|null $length The optional length limit of the field.
     * @param bool $nullable Whether the field allows null values.
     * @param bool $is_business_key Whether the field participates in a business key.
     * @param bool $is_system Whether the field is system-managed.
     * @param int $version The optimistic locking version.
     */
    public function __construct(
        public private(set) int $id,
        public private(set) string $uuid,
        public private(set) int $entity_id,
        public private(set) string $table_name,
        public private(set) string $column_name,
        public private(set) string $label,
        public private(set) string $data_type,
        public private(set) ?int $length,
        public private(set) bool $nullable,
        public private(set) bool $is_business_key,
        public private(set) bool $is_system,
        public private(set) int $version,
    ) {}
}
