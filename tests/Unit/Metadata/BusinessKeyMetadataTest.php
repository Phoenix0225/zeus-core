<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Metadata;

use Error;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\BusinessKeyMetadata;

/**
 * Class BusinessKeyMetadataTest
 *
 * Tests the hydration, type validation, and asymmetric immutability of BusinessKeyMetadata.
 */
class BusinessKeyMetadataTest extends TestCase
{
    /**
     * Test successful instantiation and property hydration of BusinessKeyMetadata.
     */
    public function test_it_hydrates_correctly(): void
    {
        $bk = new BusinessKeyMetadata(
            id: 201,
            uuid: 'c78c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            name: 'UK_sales_order_number',
            is_primary: true,
            version: 1
        );

        $this->assertSame(201, $bk->id);
        $this->assertSame('c78c1d7e-7b7d-4299-a9a7-96a8e805566f', $bk->uuid);
        $this->assertSame(42, $bk->entity_id);
        $this->assertSame('UK_sales_order_number', $bk->name);
        $this->assertTrue($bk->is_primary);
        $this->assertSame(1, $bk->version);
    }

    /**
     * Test that properties are immutable from outside (PHP 8.4 asymmetric visibility).
     */
    public function test_it_is_immutable_from_outside(): void
    {
        $bk = new BusinessKeyMetadata(
            id: 201,
            uuid: 'c78c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 42,
            name: 'UK_sales_order_number',
            is_primary: true,
            version: 1
        );

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $bk->is_primary = false;
    }
}
