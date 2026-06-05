<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

use InvalidArgumentException;
use Zeus\Core\Metadata\EntityMetadata;

/**
 * Class EntityRegistry
 *
 * In-memory registry for EntityMetadata instances, indexed by their unique code.
 */
class EntityRegistry implements RegistryInterface
{
    /**
     * @var array<string, EntityMetadata> The registered entities.
     */
    private array $items = [];

    /**
     * Registers an EntityMetadata item in the registry.
     *
     * @param mixed $item The metadata item to register.
     * @return void
     * @throws InvalidArgumentException if the item is not an instance of EntityMetadata.
     */
    public function register(mixed $item): void
    {
        if (!$item instanceof EntityMetadata) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s, got %s.',
                EntityMetadata::class,
                get_debug_type($item)
            ));
        }

        $this->items[$item->code] = $item;
    }

    /**
     * Retrieves an EntityMetadata item by its entity code.
     *
     * @param string $identifier The entity code.
     * @return EntityMetadata|null The entity metadata or null if not found.
     */
    public function get(string $identifier): ?EntityMetadata
    {
        return $this->items[$identifier] ?? null;
    }

    /**
     * Retrieves all registered EntityMetadata items.
     *
     * @return array<string, EntityMetadata>
     */
    public function all(): array
    {
        return $this->items;
    }
}
