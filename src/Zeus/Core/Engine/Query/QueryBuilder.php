<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Query;

use InvalidArgumentException;
use RuntimeException;
use Zeus\Core\Engine\Resolver\RelationResolver;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;

/**
 * Class QueryBuilder
 *
 * A fluent, metadata-validated builder for creating an EntityQuery object.
 */
class QueryBuilder
{
    private ?EntityQuery $query = null;

    public function __construct(
        private readonly EntityRegistry $entityRegistry,
        private readonly FieldRegistry $fieldRegistry,
        private readonly RelationResolver $relationResolver,
    ) {}

    /**
     * Initializes the query targeting a specific entity.
     *
     * @throws InvalidArgumentException If the entity code is not registered.
     */
    public function from(string $entityCode): self
    {
        $entity = $this->entityRegistry->get($entityCode);

        if ($entity === null) {
            throw new InvalidArgumentException(sprintf('Entity with code "%s" is not registered.', $entityCode));
        }

        $this->query = new EntityQuery($entity);

        return $this;
    }

    /**
     * Specifies which fields to select, validating that they exist for the targeted entity.
     *
     * @param array<string> $fields
     * @throws RuntimeException If called before from().
     * @throws InvalidArgumentException If a field does not belong to the entity.
     */
    public function select(array $fields): self
    {
        $this->ensureQueryStarted();

        foreach ($fields as $field) {
            if (!$this->entityHasField($this->query->entity->id, $field)) {
                throw new InvalidArgumentException(sprintf('Field "%s" does not exist on entity "%s".', $field, $this->query->entity->code));
            }
            $this->query->addSelectedField($field);
        }

        return $this;
    }

    /**
     * Adds a validated WHERE condition to the query.
     *
     * @throws RuntimeException If called before from().
     * @throws InvalidArgumentException If the field does not belong to the targeted entity.
     */
    public function where(string $field, string $operator, mixed $value): self
    {
        $this->ensureQueryStarted();

        if (!$this->entityHasField($this->query->entity->id, $field)) {
            throw new InvalidArgumentException(sprintf('Field "%s" does not exist on entity "%s".', $field, $this->query->entity->code));
        }

        $this->query->addCriteria(new QueryCriteria($field, $operator, $value));

        return $this;
    }

    /**
     * Requests a relation to be loaded alongside the entity.
     *
     * @throws RuntimeException If called before from().
     * @throws InvalidArgumentException If the requested relation does not exist for the targeted entity.
     */
    public function with(string $relationEntityCode): self
    {
        $this->ensureQueryStarted();

        $entityCode = $this->query->entity->code;
        $outgoing = $this->relationResolver->getOutgoingRelations($entityCode);
        $incoming = $this->relationResolver->getIncomingRelations($entityCode);

        $relationExists = false;

        foreach ($outgoing as $rel) {
            if ($rel->targetEntity->code === $relationEntityCode) {
                $relationExists = true;
                break;
            }
        }

        if (!$relationExists) {
            foreach ($incoming as $rel) {
                if ($rel->sourceEntity->code === $relationEntityCode) {
                    $relationExists = true;
                    break;
                }
            }
        }

        if (!$relationExists) {
            throw new InvalidArgumentException(sprintf('Relation to entity "%s" does not exist on entity "%s".', $relationEntityCode, $entityCode));
        }

        $this->query->addRelation($relationEntityCode);

        return $this;
    }

    /**
     * Returns the fully constructed and validated EntityQuery.
     *
     * @throws RuntimeException If called before from().
     */
    public function getQuery(): EntityQuery
    {
        $this->ensureQueryStarted();
        return $this->query;
    }

    private function ensureQueryStarted(): void
    {
        if ($this->query === null) {
            throw new RuntimeException('QueryBuilder must be initialized with from() before calling other methods.');
        }
    }

    private function entityHasField(int $entityId, string $columnName): bool
    {
        $fields = $this->fieldRegistry->getByEntityId($entityId);
        foreach ($fields as $field) {
            if ($field->column_name === $columnName) {
                return true;
            }
        }
        return false;
    }
}
