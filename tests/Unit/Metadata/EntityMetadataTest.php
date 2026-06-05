<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Metadata;

use Error;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\EntityMetadata;

/**
 * Class EntityMetadataTest
 *
 * Tests the hydration, type validation, and asymmetric immutability of EntityMetadata.
 */
class EntityMetadataTest extends TestCase
{
    /**
     * Test successful instantiation and property hydration of EntityMetadata.
     */
    public function test_it_hydrates_correctly(): void
    {
        $entity = new EntityMetadata(
            id: 42,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order',
            name: 'Sales Order',
            description: 'Represents a customer sales order in the ERP.',
            module_code: 'sales',
            is_active: true,
            version: 1
        );

        $this->assertSame(42, $entity->id);
        $this->assertSame('e57c1d7e-7b7d-4299-a9a7-96a8e805566f', $entity->uuid);
        $this->assertSame('sales_order', $entity->code);
        $this->assertSame('Sales Order', $entity->name);
        $this->assertSame('Represents a customer sales order in the ERP.', $entity->description);
        $this->assertSame('sales', $entity->module_code);
        $this->assertTrue($entity->is_active);
        $this->assertSame(1, $entity->version);
    }

    /**
     * Test that description can be null.
     */
    public function test_description_can_be_null(): void
    {
        $entity = new EntityMetadata(
            id: 43,
            uuid: '8a9c8b74-32ef-4b47-ad71-638bc23247c4',
            code: 'customer',
            name: 'Customer',
            description: null,
            module_code: 'crm',
            is_active: false,
            version: 2
        );

        $this->assertNull($entity->description);
    }

    /**
     * Test that properties are immutable from outside (PHP 8.4 asymmetric visibility).
     */
    public function test_it_is_immutable_from_outside(): void
    {
        $entity = new EntityMetadata(
            id: 42,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order',
            name: 'Sales Order',
            description: 'Represents a customer sales order in the ERP.',
            module_code: 'sales',
            is_active: true,
            version: 1
        );

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $entity->name = 'New Sales Order Name';
    }
}
