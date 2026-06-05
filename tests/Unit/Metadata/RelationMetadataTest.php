<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Metadata;

use Error;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\RelationMetadata;

/**
 * Class RelationMetadataTest
 *
 * Tests the hydration, type validation, and asymmetric immutability of RelationMetadata.
 */
class RelationMetadataTest extends TestCase
{
    /**
     * Test successful instantiation and property hydration of RelationMetadata.
     */
    public function test_it_hydrates_correctly(): void
    {
        $relation = new RelationMetadata(
            id: 301,
            uuid: 'd12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 42, // sales_order
            target_entity_id: 43, // customer
            relation_type: 'many-to-one',
            source_field: 'customer_id',
            target_field: 'id',
            version: 1
        );

        $this->assertSame(301, $relation->id);
        $this->assertSame('d12c1d7e-7b7d-4299-a9a7-96a8e805566f', $relation->uuid);
        $this->assertSame(42, $relation->source_entity_id);
        $this->assertSame(43, $relation->target_entity_id);
        $this->assertSame('many-to-one', $relation->relation_type);
        $this->assertSame('customer_id', $relation->source_field);
        $this->assertSame('id', $relation->target_field);
        $this->assertSame(1, $relation->version);
    }

    /**
     * Test that properties are immutable from outside (PHP 8.4 asymmetric visibility).
     */
    public function test_it_is_immutable_from_outside(): void
    {
        $relation = new RelationMetadata(
            id: 301,
            uuid: 'd12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 42,
            target_entity_id: 43,
            relation_type: 'many-to-one',
            source_field: 'customer_id',
            target_field: 'id',
            version: 1
        );

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $relation->relation_type = 'one-to-many';
    }
}
