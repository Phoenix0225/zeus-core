<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Registry;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Registry\FieldRegistry;

/**
 * Class FieldRegistryTest
 *
 * Tests the functionality of FieldRegistry.
 */
class FieldRegistryTest extends TestCase
{
    private FieldRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new FieldRegistry();
    }

    /**
     * Tests registration and logical retrieval by UUID.
     */
    public function test_it_can_register_and_retrieve(): void
    {
        $field1 = new FieldMetadata(
            id: 101,
            uuid: 'a89c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            table_name: 'sales_orders',
            column_name: 'order_number',
            label: 'Order Number',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: true,
            is_system: false,
            version: 1
        );

        $field2 = new FieldMetadata(
            id: 102,
            uuid: 'b12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            table_name: 'sales_orders',
            column_name: 'status',
            label: 'Status',
            data_type: 'string',
            length: 20,
            nullable: false,
            is_business_key: false,
            is_system: false,
            version: 1
        );

        $this->registry->register($field1);
        $this->registry->register($field2);

        $this->assertSame($field1, $this->registry->get('a89c1d7e-7b7d-4299-a9a7-96a8e805566f'));
        $this->assertSame($field2, $this->registry->get('b12c1d7e-7b7d-4299-a9a7-96a8e805566f'));
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
        $field1 = new FieldMetadata(
            id: 101,
            uuid: 'a89c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            table_name: 'sales_orders',
            column_name: 'order_number',
            label: 'Order Number',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: true,
            is_system: false,
            version: 1
        );

        $field2 = new FieldMetadata(
            id: 102,
            uuid: 'a89c1d7e-7b7d-4299-a9a7-96a8e805566f', // Same UUID
            entity_id: 42,
            table_name: 'sales_orders',
            column_name: 'another_column',
            label: 'Another field',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: false,
            is_system: false,
            version: 1
        );

        $this->registry->register($field1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field with UUID "a89c1d7e-7b7d-4299-a9a7-96a8e805566f" is already registered.');
        $this->registry->register($field2);
    }

    /**
     * Tests retrieving all elements as an associative array.
     */
    public function test_it_can_retrieve_all_registered_items(): void
    {
        $field1 = new FieldMetadata(101, 'uuid-1', 42, 'sales_orders', 'col1', 'L1', 'string', 50, false, false, false, 1);
        $field2 = new FieldMetadata(102, 'uuid-2', 42, 'sales_orders', 'col2', 'L2', 'string', 50, false, false, false, 1);

        $this->registry->register($field1);
        $this->registry->register($field2);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertSame($field1, $all['uuid-1']);
        $this->assertSame($field2, $all['uuid-2']);
    }

    /**
     * Tests that registry enforces type safety and rejects invalid objects.
     */
    public function test_it_rejects_invalid_item_types(): void
    {
        $invalidItem = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of Zeus\Core\Metadata\FieldMetadata, got stdClass.');
        $this->registry->register($invalidItem);
    }
}
