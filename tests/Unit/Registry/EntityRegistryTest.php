<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Registry;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Registry\EntityRegistry;

/**
 * Class EntityRegistryTest
 *
 * Tests the functionality of EntityRegistry.
 */
class EntityRegistryTest extends TestCase
{
    private EntityRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new EntityRegistry();
    }

    /**
     * Tests registration and logical retrieval by code.
     */
    public function test_it_can_register_and_retrieve(): void
    {
        $salesOrder = new EntityMetadata(
            id: 1,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order',
            name: 'Sales Order',
            description: 'Customer order',
            module_code: 'sales',
            is_active: true,
            version: 1
        );

        $customer = new EntityMetadata(
            id: 2,
            uuid: '8a9c8b74-32ef-4b47-ad71-638bc23247c4',
            code: 'customer',
            name: 'Customer',
            description: null,
            module_code: 'crm',
            is_active: true,
            version: 1
        );

        $this->registry->register($salesOrder);
        $this->registry->register($customer);

        $this->assertSame($salesOrder, $this->registry->get('sales_order'));
        $this->assertSame($customer, $this->registry->get('customer'));
    }

    /**
     * Tests retrieval behavior when key is not found.
     */
    public function test_it_returns_null_when_not_found(): void
    {
        $this->assertNull($this->registry->get('non_existent_entity'));
    }

    /**
     * Tests strict rejection of duplicates with identical logical identifier.
     */
    public function test_it_throws_invalid_argument_exception_on_duplicate_registration(): void
    {
        $salesOrder1 = new EntityMetadata(
            id: 1,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order',
            name: 'Sales Order',
            description: 'Customer order',
            module_code: 'sales',
            is_active: true,
            version: 1
        );

        $salesOrder2 = new EntityMetadata(
            id: 2,
            uuid: 'f87c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order', // Same logical code
            name: 'Sales Order Duplicate',
            description: 'Another customer order description',
            module_code: 'sales',
            is_active: true,
            version: 1
        );

        $this->registry->register($salesOrder1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "sales_order" is already registered.');
        $this->registry->register($salesOrder2);
    }

    /**
     * Tests retrieving all elements as an associative array.
     */
    public function test_it_can_retrieve_all_registered_items(): void
    {
        $salesOrder = new EntityMetadata(1, 'uuid-1', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $customer = new EntityMetadata(2, 'uuid-2', 'customer', 'Customer', null, 'crm', true, 1);

        $this->registry->register($salesOrder);
        $this->registry->register($customer);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertSame($salesOrder, $all['sales_order']);
        $this->assertSame($customer, $all['customer']);
    }

    /**
     * Tests that registry enforces type safety and rejects invalid objects.
     */
    public function test_it_rejects_invalid_item_types(): void
    {
        $invalidItem = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of Zeus\Core\Metadata\EntityMetadata, got stdClass.');
        $this->registry->register($invalidItem);
    }
}
