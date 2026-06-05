<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

use InvalidArgumentException;
use Zeus\Core\Metadata\BusinessKeyMetadata;

/**
 * Class BusinessKeyRegistry
 *
 * In-memory registry for BusinessKeyMetadata instances, indexed by their unique UUID.
 */
class BusinessKeyRegistry implements RegistryInterface
{
    /**
     * @var array<string, BusinessKeyMetadata> The registered business keys.
     */
    private array $items = [];

    /**
     * Registers a BusinessKeyMetadata item in the registry.
     *
     * @param mixed $item The metadata item to register.
     * @return void
     * @throws InvalidArgumentException if the item is not an instance of BusinessKeyMetadata.
     */
    public function register(mixed $item): void
    {
        if (!$item instanceof BusinessKeyMetadata) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s, got %s.',
                BusinessKeyMetadata::class,
                get_debug_type($item)
            ));
        }

        $this->items[$item->uuid] = $item;
    }

    /**
     * Retrieves a BusinessKeyMetadata item by its UUID.
     *
     * @param string $identifier The business key UUID.
     * @return BusinessKeyMetadata|null The business key metadata or null if not found.
     */
    public function get(string $identifier): ?BusinessKeyMetadata
    {
        return $this->items[$identifier] ?? null;
    }

    /**
     * Retrieves all registered BusinessKeyMetadata items.
     *
     * @return array<string, BusinessKeyMetadata>
     */
    public function all(): array
    {
        return $this->items;
    }
}
