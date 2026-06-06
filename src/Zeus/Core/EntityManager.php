<?php

declare(strict_types=1);

namespace Zeus\Core;

use Zeus\Core\Context\TenantContext;
use Zeus\Core\Context\TenantEnforcer;
use Zeus\Core\Contracts\EntityStorageInterface;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\Exceptions\UnknownFieldException;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Security\SecurityEnforcer;

class EntityManager
{
    public function __construct(
        private readonly MetadataProviderInterface $metadataProvider,
        private readonly TenantEnforcer $tenantEnforcer,
        private readonly TenantContextResolverInterface $contextResolver,
        private readonly EntityStorageInterface $storage,
        private readonly SecurityEnforcer $securityEnforcer,
        private readonly ?\Zeus\Core\Rules\RuleEngine $ruleEngine = null,
    ) {}

    public function create(EntityMetadata $entity, array $payload): string|int
    {
        $context = $this->contextResolver->resolve();
        $this->securityEnforcer->authorize($context, $entity->code, 'create');

        $fields = $this->metadataProvider->getFields($entity->code);
        $validColumns = [];
        
        foreach ($fields as $field) {
            $validColumns[] = $field->column_name;
        }

        foreach (array_keys($payload) as $key) {
            if (!in_array($key, $validColumns, true)) {
                throw new UnknownFieldException($entity->code, (string) $key);
            }
        }

        $enrichedPayload = $this->tenantEnforcer->enrichPayload($context, $payload);

        $id = $this->storage->insert($entity, $enrichedPayload);
        $record = new \Zeus\Core\Query\EntityRecord(array_merge($enrichedPayload, ['id' => $id]));
        $this->ruleEngine?->dispatch('after_create', $entity->code, $record);

        return $id;
    }

    public function update(EntityMetadata $entity, string|int $id, array $payload): bool
    {
        $context = $this->contextResolver->resolve();
        $this->securityEnforcer->authorize($context, $entity->code, 'update');

        $fields = $this->metadataProvider->getFields($entity->code);
        $validColumns = [];
        
        foreach ($fields as $field) {
            $validColumns[] = $field->column_name;
        }

        foreach (array_keys($payload) as $key) {
            if (!in_array($key, $validColumns, true)) {
                throw new UnknownFieldException($entity->code, (string) $key);
            }
        }

        $criteria = $this->tenantEnforcer->getReadCriteria($context);

        $result = $this->storage->update($entity, $id, $payload, $criteria);
        $record = new \Zeus\Core\Query\EntityRecord(array_merge($payload, ['id' => $id]));
        $this->ruleEngine?->dispatch('after_update', $entity->code, $record);

        return $result;
    }

    public function delete(EntityMetadata $entity, string|int $id): bool
    {
        $context = $this->contextResolver->resolve();
        $this->securityEnforcer->authorize($context, $entity->code, 'delete');

        $criteria = $this->tenantEnforcer->getReadCriteria($context);

        $result = $this->storage->delete($entity, $id, $criteria);
        $record = new \Zeus\Core\Query\EntityRecord(['id' => $id]);
        $this->ruleEngine?->dispatch('after_delete', $entity->code, $record);

        return $result;
    }
}
