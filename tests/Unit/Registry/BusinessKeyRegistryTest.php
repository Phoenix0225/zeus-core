<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Registry;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\BusinessKeyMetadata;
use Zeus\Core\Registry\BusinessKeyRegistry;

/**
 * Class BusinessKeyRegistryTest
 *
 * Tests the functionality of BusinessKeyRegistry.
 */
class BusinessKeyRegistryTest extends TestCase
{
    private BusinessKeyRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new BusinessKeyRegistry();
    }

    /**
     * Tests registration and logical retrieval by UUID.
     */
    public function test_it_can_register_and_retrieve(): void
    {
        $bk1 = new BusinessKeyMetadata(
            id: 201,
            uuid: 'c78c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            name: 'UK_sales_order_number',
            is_primary: true,
            version: 1
        );

        $bk2 = new BusinessKeyMetadata(
            id: 202,
            uuid: 'd89c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 43,
            name: 'UK_customer_code',
            is_primary: true,
            version: 1
        );

        $this->registry->register($bk1);
        $this->registry->register($bk2);

        $this->assertSame($bk1, $this->registry->get('c78c1d7e-7b7d-4299-a9a7-96a8e805566f'));
        $this->assertSame($bk2, $this->registry->get('d89c1d7e-7b7d-4299-a9a7-96a8e805566f'));
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
        $bk1 = new BusinessKeyMetadata(
            id: 201,
            uuid: 'c78c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            name: 'UK_sales_order_number',
            is_primary: true,
            version: 1
        );

        $bk2 = new BusinessKeyMetadata(
            id: 202,
            uuid: 'c78c1d7e-7b7d-4299-a9a7-96a8e805566f', // Same UUID
            entity_id: 42,
            name: 'UK_sales_order_number_duplicate',
            is_primary: false,
            version: 1
        );

        $this->registry->register($bk1);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Business key with UUID "c78c1d7e-7b7d-4299-a9a7-96a8e805566f" is already registered.');
        $this->registry->register($bk2);
    }

    /**
     * Tests retrieving all elements as an associative array.
     */
    public function test_it_can_retrieve_all_registered_items(): void
    {
        $bk1 = new BusinessKeyMetadata(201, 'uuid-1', 42, 'BK1', true, 1);
        $bk2 = new BusinessKeyMetadata(202, 'uuid-2', 42, 'BK2', false, 1);

        $this->registry->register($bk1);
        $this->registry->register($bk2);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertSame($bk1, $all['uuid-1']);
        $this->assertSame($bk2, $all['uuid-2']);
    }

    /**
     * Tests that registry enforces type safety and rejects invalid objects.
     */
    public function test_it_rejects_invalid_item_types(): void
    {
        $invalidItem = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of Zeus\Core\Metadata\BusinessKeyMetadata, got stdClass.');
        $this->registry->register($invalidItem);
    }
}
