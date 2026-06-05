<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

use InvalidArgumentException;
use Zeus\Core\Metadata\RelationMetadata;

/**
 * Class RelationRegistry
 *
 * In-memory registry for RelationMetadata instances, indexed by their unique UUID.
 */
class RelationRegistry implements RegistryInterface
{
    /**
     * @var array<string, RelationMetadata> The registered relations.
     */
    private array $items = [];

    /**
     * Registers a RelationMetadata item in the registry.
     *
     * @param mixed $item The metadata item to register.
     * @return void
     * @throws InvalidArgumentException if the item is not an instance of RelationMetadata.
     */
    public function register(mixed $item): void
    {
        if (!$item instanceof RelationMetadata) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s, got %s.',
                RelationMetadata::class,
                get_debug_type($item)
            ));
        }

        if (isset($this->items[$item->uuid])) {
            throw new InvalidArgumentException(sprintf(
                'Relation with UUID "%s" is already registered.',
                $item->uuid
            ));
        }

        $this->items[$item->uuid] = $item;
    }

    /**
     * Retrieves a RelationMetadata item by its UUID.
     *
     * @param string $identifier The relation UUID.
     * @return RelationMetadata|null The relation metadata or null if not found.
     */
    public function get(string $identifier): ?RelationMetadata
    {
        return $this->items[$identifier] ?? null;
    }

    /**
     * Retrieves all registered RelationMetadata items.
     *
     * @return array<string, RelationMetadata>
     */
    public function all(): array
    {
        return $this->items;
    }
}
