<?php

declare(strict_types=1);

namespace Zeus\Core\Contracts;

use Zeus\Core\Metadata\EntityMetadata;

interface EntityStorageInterface
{
    public function insert(EntityMetadata $entity, array $payload): string|int;
    public function update(EntityMetadata $entity, string|int $id, array $payload, array $tenantCriteria): bool;
    public function delete(EntityMetadata $entity, string|int $id, array $tenantCriteria): bool;
}
