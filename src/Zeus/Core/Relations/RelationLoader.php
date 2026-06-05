<?php

declare(strict_types=1);

namespace Zeus\Core\Relations;

use Zeus\Core\EntityReader;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Query\EntityQueryBuilder;
use Zeus\Core\Query\EntityRecord;

class RelationLoader
{
    /**
     * @param EntityRecord[] $records
     * @return EntityRecord[]
     */
    public function loadBelongsTo(
        array $records,
        RelationMetadata $relation,
        EntityMetadata $targetEntity,
        EntityQueryBuilder $builder,
        EntityReader $reader
    ): array {
        $ids = array_values(
            array_unique(
                array_filter(
                    array_map(fn($r) => $r->get($relation->localKey), $records)
                )
            )
        );

        if (empty($ids)) {
            return $records;
        }

        $query = $builder->forEntity($targetEntity)
            ->whereIn($relation->foreignKey, $ids)
            ->getQuery();

        $relatedRecords = $reader->fetch($query);

        $dict = [];
        foreach ($relatedRecords as $rel) {
            $dict[$rel->get($relation->foreignKey)] = $rel;
        }

        return array_map(function (EntityRecord $record) use ($relation, $dict) {
            $foreignId = $record->get($relation->localKey);
            return $record->withRelation($relation->name, $dict[$foreignId] ?? null);
        }, $records);
    }
}
