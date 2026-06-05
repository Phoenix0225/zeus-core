<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Registry;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class RelationRegistryTest
 *
 * Tests the functionality of RelationRegistry.
 */
class RelationRegistryTest extends TestCase
{
    private RelationRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RelationRegistry();
    }

    /**
     * Tests registration and logical retrieval by UUID.
     */
    public function test_it_can_register_and_retrieve(): void
    {
        $relation1 = new RelationMetadata(
            id: 301,
            uuid: 'd12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 42,
            target_entity_id: 43,
            relation_type: 'many-to-one',
            source_field: 'customer_id',
            target_field: 'id',
            version: 1
        );

        $relation2 = new RelationMetadata(
            id: 302,
            uuid: 'e23c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 42,
            target_entity_id: 44,
            relation_type: 'one-to-many',
            source_field: 'id',
            target_field: 'order_id',
            version: 1
        );

        $this->registry->register($relation1);
        $this->registry->register($relation2);

        $this->assertSame($relation1, $this->registry->get('d12c1d7e-7b7d-4299-a9a7-96a8e805566f'));
        $this->assertSame($relation2, $this->registry->get('e23c1d7e-7b7d-4299-a9a7-96a8e805566f'));
    }

    /**
     * Tests retrieval behavior when key is not found.
     */
    public function test_it_returns_null_when_not_found(): void
    {
        $this->assertNull($this->registry->get('non_existent_uuid'));
    }

    /**
     * Tests strict rejection of duplicates with identical logical identifier.
     */
    public function test_it_throws_invalid_argument_exception_on_duplicate_registration(): void
    {
        $relation1 = new RelationMetadata(
            id: 301,
            uuid: 'd12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 42,
            target_entity_id: 43,
            relation_type: 'many-to-one',
            source_field: 'customer_id',
            target_field: 'id',
            version: 1
        );

        $relation2 = new RelationMetadata(
            id: 302,
            uuid: 'd12c1d7e-7b7d-4299-a9a7-96a8e805566f', // Same UUID
            source_entity_id: 42,
            target_entity_id: 43,
            relation_type: 'one-to-many',
            source_field: 'id',
            target_field: 'order_id',
            version: 1
        );

        $this->registry->register($relation1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relation with UUID "d12c1d7e-7b7d-4299-a9a7-96a8e805566f" is already registered.');
        $this->registry->register($relation2);
    }

    /**
     * Tests retrieving all elements as an associative array.
     */
    public function test_it_can_retrieve_all_registered_items(): void
    {
        $relation1 = new RelationMetadata(301, 'uuid-1', 42, 43, 'many-to-one', 'customer_id', 'id', 1);
        $relation2 = new RelationMetadata(302, 'uuid-2', 42, 44, 'one-to-many', 'id', 'order_id', 1);

        $this->registry->register($relation1);
        $this->registry->register($relation2);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertSame($relation1, $all['uuid-1']);
        $this->assertSame($relation2, $all['uuid-2']);
    }

    /**
     * Tests that registry enforces type safety and rejects invalid objects.
     */
    public function test_it_rejects_invalid_item_types(): void
    {
        $invalidItem = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of Zeus\Core\Metadata\RelationMetadata, got stdClass.');
        $this->registry->register($invalidItem);
    }
}
