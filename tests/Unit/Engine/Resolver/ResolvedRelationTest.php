<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Resolver;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Resolver\ResolvedRelation;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;

/**
 * Class ResolvedRelationTest
 *
 * Tests the instantiation and immutability of the ResolvedRelation DTO.
 */
class ResolvedRelationTest extends TestCase
{
    /**
     * Test successful instantiation and property mapping of ResolvedRelation.
     */
    public function test_it_instantiates_correctly(): void
    {
        $sourceEntity = new EntityMetadata(10, 'uuid-src', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $targetEntity = new EntityMetadata(20, 'uuid-trg', 'customer', 'Customer', null, 'crm', true, 1);
        $sourceField = new FieldMetadata(100, 'f-so-cust-code', 10, 'sales_orders', 'customer_code', 'Customer Code', 'string', 50, false, false, false, 1);
        $targetField = new FieldMetadata(200, 'f-cust-code', 20, 'customers', 'code', 'Code', 'string', 50, false, true, false, 1);

        $resolved = new ResolvedRelation(
            sourceEntity: $sourceEntity,
            targetEntity: $targetEntity,
            sourceField: $sourceField,
            targetField: $targetField,
            relationType: 'N:1'
        );

        $this->assertSame($sourceEntity, $resolved->sourceEntity);
        $this->assertSame($targetEntity, $resolved->targetEntity);
        $this->assertSame($sourceField, $resolved->sourceField);
        $this->assertSame($targetField, $resolved->targetField);
        $this->assertSame('N:1', $resolved->relationType);
    }
}
