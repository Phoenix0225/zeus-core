<?php

declare(strict_types=1);

namespace Zeus\Core\Registry;

/**
 * Interface RegistryInterface
 *
 * Defines the contract for an in-memory metadata registry in zeus-core.
 */
interface RegistryInterface
{
    /**
     * Registers a metadata item in the registry.
     *
     * @param mixed $item The metadata item to register.
     * @return void
     * @throws \InvalidArgumentException if the item's type is invalid for the registry.
     */
    public function register(mixed $item): void;

    /**
     * Retrieves a metadata item by its logical identifier.
     *
     * @param string $identifier The logical identifier (e.g. entity code, UUID).
     * @return mixed The matching metadata item, or null if not found.
     */
    public function get(string $identifier): mixed;

    /**
     * Retrieves all registered metadata items.
     *
     * @return array<string, mixed> An associative array of registered metadata items.
     */
    public function all(): array;
}
