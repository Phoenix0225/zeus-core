<?php

declare(strict_types=1);

namespace Zeus\Core\Query;

use RuntimeException;
use Zeus\Core\Context\TenantEnforcer;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Security\SecurityEnforcer;

class EntityQueryBuilder
{
    private ?EntityQuery $query = null;

    public function __construct(
        private readonly TenantEnforcer $tenantEnforcer,
        private readonly TenantContextResolverInterface $contextResolver,
        private readonly SecurityEnforcer $securityEnforcer,
    ) {}

    public function forEntity(EntityMetadata $entity): self
    {
        $this->query = new EntityQuery($entity);

        $context = $this->contextResolver->resolve();
        $this->securityEnforcer->authorize($context, $entity->code, 'read');
        $criteria = $this->tenantEnforcer->getReadCriteria($context);

        foreach ($criteria as $criterion) {
            $this->query->addCondition(new Condition(
                field: $criterion['field'],
                operator: '=',
                value: $criterion['value'],
                allowNull: $criterion['allow_null'] ?? false
            ));
        }

        return $this;
    }

    public function where(string $field, string $operator, mixed $value): self
    {
        if ($this->query === null) {
            throw new RuntimeException("Vous devez appeler forEntity() avant d'ajouter des conditions.");
        }

        $this->query->addCondition(new Condition($field, $operator, $value));

        return $this;
    }

    public function whereIn(string $field, array $values): self
    {
        if ($this->query === null) {
            throw new RuntimeException("Vous devez appeler forEntity() avant d'ajouter des conditions.");
        }

        $this->query->addCondition(new Condition($field, 'IN', $values));

        return $this;
    }

    public function getQuery(): EntityQuery
    {
        if ($this->query === null) {
            throw new RuntimeException("La requête n'a pas été initialisée avec forEntity().");
        }

        return $this->query;
    }
}
