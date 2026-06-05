<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Resolver;

use InvalidArgumentException;
use RuntimeException;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class RelationResolver
 *
 * Resolves entity relations based on metadata mappings, without reliance on database foreign keys.
 */
class RelationResolver
{
    /**
     * RelationResolver constructor.
     *
     * @param EntityRegistry $entityRegistry Registered entities definitions.
     * @param RelationRegistry $relationRegistry Registered relations definitions.
     * @param FieldRegistry $fieldRegistry Registered fields definitions.
     */
    public function __construct(
        private readonly EntityRegistry $entityRegistry,
        private readonly RelationRegistry $relationRegistry,
        private readonly FieldRegistry $fieldRegistry
    ) {}

    /**
     * Resolves all relations where the specified entity is the source.
     *
     * @param string $entityCode The code of the source entity.
     * @return array<ResolvedRelation>
     * @throws InvalidArgumentException if the entity code is not registered.
     * @throws RuntimeException if relations mapping points to unregistered target entities or fields.
     */
    public function getOutgoingRelations(string $entityCode): array
    {
        $sourceEntity = $this->entityRegistry->get($entityCode);

        if ($sourceEntity === null) {
            throw new InvalidArgumentException(sprintf(
                'Entity with code "%s" is not registered.',
                $entityCode
            ));
        }

        $resolvedRelations = [];

        foreach ($this->relationRegistry->all() as $relation) {
            if ($relation->source_entity_id === $sourceEntity->id) {
                $targetEntity = $this->findEntityById($relation->target_entity_id);
                if ($targetEntity === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Target entity ID %d in relation "%s" not found.',
                        $relation->target_entity_id,
                        $relation->uuid
                    ));
                }

                $sourceField = $this->findFieldByName($relation->source_field, $sourceEntity->id);
                if ($sourceField === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Source field "%s" on entity "%s" in relation "%s" not found.',
                        $relation->source_field,
                        $sourceEntity->code,
                        $relation->uuid
                    ));
                }

                $targetField = $this->findFieldByName($relation->target_field, $targetEntity->id);
                if ($targetField === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Target field "%s" on entity "%s" in relation "%s" not found.',
                        $relation->target_field,
                        $targetEntity->code,
                        $relation->uuid
                    ));
                }

                $resolvedRelations[] = new ResolvedRelation(
                    sourceEntity: $sourceEntity,
                    targetEntity: $targetEntity,
                    sourceField: $sourceField,
                    targetField: $targetField,
                    relationType: $relation->relation_type
                );
            }
        }

        return $resolvedRelations;
    }

    /**
     * Resolves all relations where the specified entity is the target.
     *
     * @param string $entityCode The code of the target entity.
     * @return array<ResolvedRelation>
     * @throws InvalidArgumentException if the entity code is not registered.
     * @throws RuntimeException if relations mapping points to unregistered source entities or fields.
     */
    public function getIncomingRelations(string $entityCode): array
    {
        $targetEntity = $this->entityRegistry->get($entityCode);

        if ($targetEntity === null) {
            throw new InvalidArgumentException(sprintf(
                'Entity with code "%s" is not registered.',
                $entityCode
            ));
        }

        $resolvedRelations = [];

        foreach ($this->relationRegistry->all() as $relation) {
            if ($relation->target_entity_id === $targetEntity->id) {
                $sourceEntity = $this->findEntityById($relation->source_entity_id);
                if ($sourceEntity === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Source entity ID %d in relation "%s" not found.',
                        $relation->source_entity_id,
                        $relation->uuid
                    ));
                }

                $sourceField = $this->findFieldByName($relation->source_field, $sourceEntity->id);
                if ($sourceField === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Source field "%s" on entity "%s" in relation "%s" not found.',
                        $relation->source_field,
                        $sourceEntity->code,
                        $relation->uuid
                    ));
                }

                $targetField = $this->findFieldByName($relation->target_field, $targetEntity->id);
                if ($targetField === null) {
                    throw new RuntimeException(sprintf(
                        'Metadata integrity error: Target field "%s" on entity "%s" in relation "%s" not found.',
                        $relation->target_field,
                        $targetEntity->code,
                        $relation->uuid
                    ));
                }

                $resolvedRelations[] = new ResolvedRelation(
                    sourceEntity: $sourceEntity,
                    targetEntity: $targetEntity,
                    sourceField: $sourceField,
                    targetField: $targetField,
                    relationType: $relation->relation_type
                );
            }
        }

        return $resolvedRelations;
    }

    /**
     * Helper to find an entity by its ID.
     */
    private function findEntityById(int $id): ?EntityMetadata
    {
        foreach ($this->entityRegistry->all() as $entity) {
            if ($entity->id === $id) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Helper to find a field by name and entity ID.
     */
    private function findFieldByName(string $name, int $entityId): ?FieldMetadata
    {
        foreach ($this->fieldRegistry->all() as $field) {
            if ($field->column_name === $name && $field->entity_id === $entityId) {
                return $field;
            }
        }

        return null;
    }
}
