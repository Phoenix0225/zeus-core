<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Query;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Query\QueryBuilder;
use Zeus\Core\Engine\Resolver\RelationResolver;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;

    protected function setUp(): void
    {
        $entityRegistry = new EntityRegistry();
        $relationRegistry = new RelationRegistry();
        $fieldRegistry = new FieldRegistry();
        $relationResolver = new RelationResolver(
            $entityRegistry,
            $relationRegistry,
            $fieldRegistry
        );

        $this->builder = new QueryBuilder($entityRegistry, $fieldRegistry, $relationResolver);

        // Entities
        $salesOrder = new EntityMetadata(10, 'uuid-1', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $customer = new EntityMetadata(20, 'uuid-2', 'customer', 'Customer', null, 'crm', true, 1);
        $entityRegistry->register($salesOrder);
        $entityRegistry->register($customer);

        // Fields
        $orderNumber = new FieldMetadata(101, 'f-1', 10, 'sales_orders', 'order_number', 'Order #', 'string', 50, false, false, false, 1);
        $totalAmount = new FieldMetadata(102, 'f-2', 10, 'sales_orders', 'total_amount', 'Total', 'float', null, false, false, false, 1);
        $customerCode = new FieldMetadata(103, 'f-3', 10, 'sales_orders', 'customer_code', 'Customer Code', 'string', 50, false, false, false, 1);
        $custCode = new FieldMetadata(201, 'f-4', 20, 'customers', 'code', 'Code', 'string', 50, false, true, false, 1);
        
        $fieldRegistry->register($orderNumber);
        $fieldRegistry->register($totalAmount);
        $fieldRegistry->register($customerCode);
        $fieldRegistry->register($custCode);

        // Relation
        $relation = new RelationMetadata(301, 'r-1', 10, 20, 'N:1', 'customer_code', 'code', 1);
        $relationRegistry->register($relation);
    }

    public function test_it_can_build_a_valid_query(): void
    {
        $query = $this->builder
            ->from('sales_order')
            ->select(['order_number'])
            ->where('total_amount', '>', 100)
            ->with('customer')
            ->getQuery();

        $this->assertSame('sales_order', $query->entity->code);
        $this->assertSame(['order_number'], $query->selectedFields);
        $this->assertCount(1, $query->criteria);
        $this->assertSame('total_amount', $query->criteria[0]->field);
        $this->assertSame('>', $query->criteria[0]->operator);
        $this->assertSame(100, $query->criteria[0]->value);
        $this->assertSame(['customer'], $query->relationsToLoad);
    }

    public function test_it_throws_exception_if_entity_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "unknown" is not registered.');

        $this->builder->from('unknown');
    }

    public function test_it_throws_exception_if_selected_field_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "champ_imaginaire" does not exist on entity "sales_order".');

        $this->builder->from('sales_order')->select(['champ_imaginaire']);
    }

    public function test_it_throws_exception_if_where_field_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "champ_imaginaire" does not exist on entity "sales_order".');

        $this->builder->from('sales_order')->where('champ_imaginaire', '=', 'valeur');
    }

    public function test_it_throws_exception_if_relation_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relation to entity "entite_sans_relation" does not exist on entity "sales_order".');

        $this->builder->from('sales_order')->with('entite_sans_relation');
    }
}
