<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Metadata;

use Error;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\FieldMetadata;

/**
 * Class FieldMetadataTest
 *
 * Tests the hydration, type validation, and asymmetric immutability of FieldMetadata.
 */
class FieldMetadataTest extends TestCase
{
    /**
     * Test successful instantiation and property hydration of FieldMetadata.
     */
    public function test_it_hydrates_correctly(): void
    {
        $field = new FieldMetadata(
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

        $this->assertSame(101, $field->id);
        $this->assertSame('a89c1d7e-7b7d-4299-a9a7-96a8e805566f', $field->uuid);
        $this->assertSame(42, $field->entity_id);
        $this->assertSame('sales_orders', $field->table_name);
        $this->assertSame('order_number', $field->column_name);
        $this->assertSame('Order Number', $field->label);
        $this->assertSame('string', $field->data_type);
        $this->assertSame(50, $field->length);
        $this->assertFalse($field->nullable);
        $this->assertTrue($field->is_business_key);
        $this->assertFalse($field->is_system);
        $this->assertSame(1, $field->version);
    }

    /**
     * Test that length can be null.
     */
    public function test_length_can_be_null(): void
    {
        $field = new FieldMetadata(
            id: 102,
            uuid: 'b12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            table_name: 'sales_orders',
            column_name: 'notes',
            label: 'Order Notes',
            data_type: 'text',
            length: null,
            nullable: true,
            is_business_key: false,
            is_system: false,
            version: 1
        );

        $this->assertNull($field->length);
        $this->assertTrue($field->nullable);
    }

    /**
     * Test that properties are immutable from outside (PHP 8.4 asymmetric visibility).
     */
    public function test_it_is_immutable_from_outside(): void
    {
        $field = new FieldMetadata(
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

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $field->column_name = 'new_column_name';
    }
}
