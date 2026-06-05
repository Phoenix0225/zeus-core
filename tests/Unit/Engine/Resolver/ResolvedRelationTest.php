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
        $sourceEntity = new EntityMetadata(1, 'uuid-source', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $targetEntity = new EntityMetadata(2, 'uuid-target', 'customer', 'Customer', null, 'crm', true, 1);
        $sourceField = new FieldMetadata(101, 'uuid-field-src', 1, 'sales_orders', 'customer_id', 'Customer ID', 'int', null, false, false, false, 1);
        $targetField = new FieldMetadata(102, 'uuid-field-trg', 2, 'customers', 'id', 'ID', 'int', null, false, false, false, 1);

        $resolved = new ResolvedRelation(
            sourceEntity: $sourceEntity,
            targetEntity: $targetEntity,
            sourceField: $sourceField,
            targetField: $targetField,
            relationType: 'many-to-one'
        );

        $this->assertSame($sourceEntity, $resolved->sourceEntity);
        $this->assertSame($targetEntity, $resolved->targetEntity);
        $this->assertSame($sourceField, $resolved->sourceField);
        $this->assertSame($targetField, $resolved->targetField);
        $this->assertSame('many-to-one', $resolved->relationType);
    }
}
