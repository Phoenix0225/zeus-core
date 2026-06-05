<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Resolver;

use InvalidArgumentException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Resolver\RelationResolver;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class RelationResolverTest
 *
 * Tests incoming and outgoing relation resolution, exception handling, and metadata checks.
 */
class RelationResolverTest extends TestCase
{
    private EntityRegistry $entityRegistry;
    private RelationRegistry $relationRegistry;
    private FieldRegistry $fieldRegistry;
    private RelationResolver $resolver;

    protected function setUp(): void
    {
        $this->entityRegistry = new EntityRegistry();
        $this->relationRegistry = new RelationRegistry();
        $this->fieldRegistry = new FieldRegistry();
        $this->resolver = new RelationResolver(
            $this->entityRegistry,
            $this->relationRegistry,
            $this->fieldRegistry
        );

        // Register entities
        $this->entityRegistry->register(new EntityMetadata(1, 'uuid-sales-order', 'sales_order', 'Sales Order', null, 'sales', true, 1));
        $this->entityRegistry->register(new EntityMetadata(2, 'uuid-customer', 'customer', 'Customer', null, 'crm', true, 1));
        $this->entityRegistry->register(new EntityMetadata(3, 'uuid-invoice', 'invoice', 'Invoice', null, 'billing', true, 1));

        // Register fields
        $this->fieldRegistry->register(new FieldMetadata(101, 'f-so-id', 1, 'sales_orders', 'id', 'ID', 'int', null, false, false, false, 1));
        $this->fieldRegistry->register(new FieldMetadata(102, 'f-so-cust-id', 1, 'sales_orders', 'customer_id', 'Customer ID', 'int', null, false, false, false, 1));
        $this->fieldRegistry->register(new FieldMetadata(103, 'f-cust-id', 2, 'customers', 'id', 'ID', 'int', null, false, false, false, 1));
        $this->fieldRegistry->register(new FieldMetadata(104, 'f-inv-so-id', 3, 'invoices', 'order_id', 'Order ID', 'int', null, false, false, false, 1));

        // Register relations
        // 1. sales_order -> customer (outgoing from sales_order, incoming to customer)
        $this->relationRegistry->register(new RelationMetadata(
            id: 301,
            uuid: 'r-so-to-cust',
            source_entity_id: 1,
            target_entity_id: 2,
            relation_type: 'many-to-one',
            source_field: 'customer_id',
            target_field: 'id',
            version: 1
        ));

        // 2. invoice -> sales_order (incoming to sales_order, outgoing from invoice)
        $this->relationRegistry->register(new RelationMetadata(
            id: 302,
            uuid: 'r-inv-to-so',
            source_entity_id: 3,
            target_entity_id: 1,
            relation_type: 'many-to-one',
            source_field: 'order_id',
            target_field: 'id',
            version: 1
        ));
    }

    /**
     * Test successful resolution of outgoing relations.
     */
    public function test_it_resolves_outgoing_relations_successfully(): void
    {
        $relations = $this->resolver->getOutgoingRelations('sales_order');

        $this->assertCount(1, $relations);
        $resolved = $relations[0];

        $this->assertSame('sales_order', $resolved->sourceEntity->code);
        $this->assertSame('customer', $resolved->targetEntity->code);
        $this->assertSame('customer_id', $resolved->sourceField->column_name);
        $this->assertSame('id', $resolved->targetField->column_name);
        $this->assertSame('many-to-one', $resolved->relationType);
    }

    /**
     * Test successful resolution of incoming relations.
     */
    public function test_it_resolves_incoming_relations_successfully(): void
    {
        $relations = $this->resolver->getIncomingRelations('sales_order');

        $this->assertCount(1, $relations);
        $resolved = $relations[0];

        $this->assertSame('invoice', $resolved->sourceEntity->code);
        $this->assertSame('sales_order', $resolved->targetEntity->code);
        $this->assertSame('order_id', $resolved->sourceField->column_name);
        $this->assertSame('id', $resolved->targetField->column_name);
        $this->assertSame('many-to-one', $resolved->relationType);
    }

    /**
     * Test that resolving outgoing relations for an unregistered entity throws InvalidArgumentException.
     */
    public function test_it_throws_exception_if_source_entity_not_found_on_outgoing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "purchase_order" is not registered.');

        $this->resolver->getOutgoingRelations('purchase_order');
    }

    /**
     * Test that resolving incoming relations for an unregistered entity throws InvalidArgumentException.
     */
    public function test_it_throws_exception_if_target_entity_not_found_on_incoming(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "purchase_order" is not registered.');

        $this->resolver->getIncomingRelations('purchase_order');
    }

    /**
     * Test that resolution throws RuntimeException when a relation points to a target entity that is missing.
     */
    public function test_it_throws_runtime_exception_on_broken_target_entity_link(): void
    {
        // Add a broken relation referencing non-existent target entity ID 999
        $this->relationRegistry->register(new RelationMetadata(
            id: 303,
            uuid: 'r-broken',
            source_entity_id: 1,
            target_entity_id: 999, // Unknown target
            relation_type: 'one-to-one',
            source_field: 'id',
            target_field: 'id',
            version: 1
        ));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Metadata integrity error: Target entity ID 999 in relation "r-broken" not found.');

        $this->resolver->getOutgoingRelations('sales_order');
    }

    /**
     * Test that resolution throws RuntimeException when a relation points to a source field that is missing.
     */
    public function test_it_throws_runtime_exception_on_broken_source_field_link(): void
    {
        // Add a relation referencing non-existent source field 'unknown_field'
        $this->relationRegistry->register(new RelationMetadata(
            id: 304,
            uuid: 'r-broken-field',
            source_entity_id: 1,
            target_entity_id: 2,
            relation_type: 'one-to-one',
            source_field: 'unknown_field', // Unknown field on source
            target_field: 'id',
            version: 1
        ));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Metadata integrity error: Source field "unknown_field" on entity "sales_order" in relation "r-broken-field" not found.');

        $this->resolver->getOutgoingRelations('sales_order');
    }
}
