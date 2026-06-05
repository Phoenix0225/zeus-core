<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

use InvalidArgumentException;
use Zeus\Core\Metadata\FieldMetadata;

/**
 * Class FieldRegistry
 *
 * In-memory registry for FieldMetadata instances, indexed by their unique UUID.
 */
class FieldRegistry implements RegistryInterface
{
    /**
     * @var array<string, FieldMetadata> The registered fields.
     */
    private array $items = [];

    /**
     * @var array<int, array<FieldMetadata>> Indexed by entity_id
     */
    private array $byEntityId = [];

    /**
     * Registers a FieldMetadata item in the registry.
     *
     * @param mixed $item The metadata item to register.
     * @return void
     * @throws InvalidArgumentException if the item is not an instance of FieldMetadata.
     */
    public function register(mixed $item): void
    {
        if (!$item instanceof FieldMetadata) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s, got %s.',
                FieldMetadata::class,
                get_debug_type($item)
            ));
        }

        if (isset($this->items[$item->uuid])) {
            throw new InvalidArgumentException(sprintf(
                'Field with UUID "%s" is already registered.',
                $item->uuid
            ));
        }

        $this->items[$item->uuid] = $item;

        if (!isset($this->byEntityId[$item->entity_id])) {
            $this->byEntityId[$item->entity_id] = [];
        }
        $this->byEntityId[$item->entity_id][] = $item;
    }

    /**
     * Retrieves a FieldMetadata item by its UUID.
     *
     * @param string $identifier The field UUID.
     * @return FieldMetadata|null The field metadata or null if not found.
     */
    public function get(string $identifier): ?FieldMetadata
    {
        return $this->items[$identifier] ?? null;
    }

    /**
     * Retrieves all registered FieldMetadata items.
     *
     * @return array<string, FieldMetadata>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Retrieves all fields for a specific entity.
     *
     * @param int $entityId The ID of the entity.
     * @return array<FieldMetadata>
     */
    public function getByEntityId(int $entityId): array
    {
        return $this->byEntityId[$entityId] ?? [];
    }
}
