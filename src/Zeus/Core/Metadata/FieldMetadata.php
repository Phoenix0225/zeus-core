<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

use Zeus\Core\Metadata\Enums\FieldType;

/**
 * Class FieldMetadata
 *
 * Represents the metadata schema for an entity field.
 * Uses PHP 8.4 asymmetric visibility to ensure immutability from the outside
 * once hydrated.
 */
readonly class FieldMetadata
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
        public int $id,
        public string $uuid,
        public int $entity_id,
        public string $table_name,
        public string $column_name,
        public FieldType $type,
        public string $label,
        public string $data_type,
        public ?int $length,
        public bool $nullable,
        public bool $is_business_key,
        public bool $is_system,
        public int $version,
        public array $options = [],
    ) {}
}
