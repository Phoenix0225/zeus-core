<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Relations;

use PHPUnit\Framework\TestCase;
use Zeus\Core\EntityReader;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\Enums\RelationType;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Query\EntityQuery;
use Zeus\Core\Query\EntityQueryBuilder;
use Zeus\Core\Query\EntityRecord;
use Zeus\Core\Relations\RelationLoader;

class RelationLoaderTest extends TestCase
{
    public function test_it_eager_loads_belongs_to_relations_without_n_plus_one_issue(): void
    {
        $loader = new RelationLoader();

        $records = [
            new EntityRecord(['id' => 10, 'operator_id' => 1]),
            new EntityRecord(['id' => 11, 'operator_id' => 2]),
            new EntityRecord(['id' => 12, 'operator_id' => 1]),
        ];

        $relation = new RelationMetadata(
            name: 'operator',
            type: RelationType::BELONGS_TO,
            targetEntityCode: 'operator',
            localKey: 'operator_id',
            foreignKey: 'id'
        );

        $targetEntity = new EntityMetadata(
            id: 2,
            uuid: 'uuid-op',
            code: 'operator',
            name: 'Operator',
            description: null,
            module_code: 'core',
            is_active: true,
            version: 1
        );

        $mockQuery = new EntityQuery($targetEntity);

        $builder = $this->createMock(EntityQueryBuilder::class);
        $builder->method('forEntity')->with($targetEntity)->willReturnSelf();
        $builder->method('whereIn')->with('id', [1, 2])->willReturnSelf();
        $builder->method('getQuery')->willReturn($mockQuery);

        $reader = $this->createMock(EntityReader::class);
        $reader->method('fetch')->with($mockQuery)->willReturn([
            new EntityRecord(['id' => 1, 'name' => 'Operator A']),
            new EntityRecord(['id' => 2, 'name' => 'Operator B']),
        ]);

        $result = $loader->loadBelongsTo($records, $relation, $targetEntity, $builder, $reader);

        $this->assertCount(3, $result);
        
        // Assert immutability
        $this->assertNotSame($records[0], $result[0]);
        $this->assertNull($records[0]->getRelation('operator'));

        // Assert relations loaded correctly
        $this->assertInstanceOf(EntityRecord::class, $result[0]->getRelation('operator'));
        $this->assertEquals('Operator A', $result[0]->getRelation('operator')->get('name'));

        $this->assertInstanceOf(EntityRecord::class, $result[1]->getRelation('operator'));
        $this->assertEquals('Operator B', $result[1]->getRelation('operator')->get('name'));

        $this->assertInstanceOf(EntityRecord::class, $result[2]->getRelation('operator'));
        $this->assertEquals('Operator A', $result[2]->getRelation('operator')->get('name'));
    }
}
