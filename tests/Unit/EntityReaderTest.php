<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Contracts\EntityQueryExecutorInterface;
use Zeus\Core\EntityReader;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Query\EntityQuery;

class EntityReaderTest extends TestCase
{
    public function test_it_fetches_and_maps_to_entity_records(): void
    {
        $executor = $this->createMock(EntityQueryExecutorInterface::class);
        $executor->method('execute')->willReturn([
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);

        $reader = new EntityReader($executor);
        
        $entityMetadata = new EntityMetadata(
            id: 1,
            uuid: 'some-uuid',
            code: 'test',
            name: 'Test',
            description: null,
            module_code: 'test',
            is_active: true,
            version: 1
        );
        $query = new EntityQuery($entityMetadata);

        $results = $reader->fetch($query);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('A', $results[0]->get('name'));
        $this->assertEquals('B', $results[1]->get('name'));
    }
}
