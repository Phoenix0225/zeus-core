<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Engine\Query\EntityQuery;

/**
 * Interface StorageInterface
 *
 * Contract defining how the core engine interacts with the persistent storage layer.
 */
interface StorageInterface
{
    /**
     * Inserts a new record for the given entity.
     *
     * @param string $entityCode
     * @param array<string, mixed> $data
     * @return int|string The generated primary key/ID.
     */
    public function insert(string $entityCode, array $data): int|string;

    /**
     * Updates an existing record for the given entity.
     *
     * @param string $entityCode
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return bool True on success, false otherwise.
     */
    public function update(string $entityCode, int|string $id, array $data): bool;

    /**
     * Deletes a record for the given entity.
     *
     * @param string $entityCode
     * @param int|string $id
     * @return bool True on success, false otherwise.
     */
    public function delete(string $entityCode, int|string $id): bool;

    /**
     * Executes a fully built EntityQuery and returns the results.
     *
     * @param EntityQuery $query
     * @return array<array<string, mixed>> The result set.
     */
    public function query(EntityQuery $query): array;
}
