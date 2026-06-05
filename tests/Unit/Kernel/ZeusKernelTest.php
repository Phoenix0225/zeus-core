<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Kernel;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Kernel\ZeusKernel;
use Zeus\Core\Metadata\BusinessKeyMetadata;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Registry\BusinessKeyRegistry;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class ZeusKernelTest
 *
 * Tests the initialization, loading of metadata, registries wiring, and state checks
 * of the ZeusKernel class.
 */
class ZeusKernelTest extends TestCase
{
    private EntityRegistry $entityRegistry;
    private FieldRegistry $fieldRegistry;
    private BusinessKeyRegistry $businessKeyRegistry;
    private RelationRegistry $relationRegistry;

    protected function setUp(): void
    {
        $this->entityRegistry = new EntityRegistry();
        $this->fieldRegistry = new FieldRegistry();
        $this->businessKeyRegistry = new BusinessKeyRegistry();
        $this->relationRegistry = new RelationRegistry();
    }

    /**
     * Create a dummy MetadataProviderInterface with the specified metadata datasets.
     */
    private function createMockProvider(
        array $entities = [],
        array $fields = [],
        array $keys = [],
        array $relations = []
    ): MetadataProviderInterface {
        return new class($entities, $fields, $keys, $relations) implements MetadataProviderInterface {
            public function __construct(
                private array $entities,
                private array $fields,
                private array $keys,
                private array $relations
            ) {}

            public function getEntities(): iterable
            {
                return $this->entities;
            }

            public function getFields(): iterable
            {
                return $this->fields;
            }

            public function getBusinessKeys(): iterable
            {
                return $this->keys;
            }

            public function getRelations(): iterable
            {
                return $this->relations;
            }
        };
    }

    /**
     * Test successful boot cycle of the Kernel.
     */
    public function test_it_boots_successfully_and_populates_registries(): void
    {
        $entity = new EntityMetadata(1, 'uuid-entity', 'sales_order', 'Sales Order', null, 'sales', true, 1);
        $field = new FieldMetadata(101, 'uuid-field', 1, 'sales_orders', 'col1', 'L1', 'string', 50, false, false, false, 1);
        $key = new BusinessKeyMetadata(201, 'uuid-key', 1, 'BK1', true, 1);
        $relation = new RelationMetadata(301, 'uuid-relation', 1, 2, 'many-to-one', 'customer_id', 'id', 1);

        $provider = $this->createMockProvider(
            entities: [$entity],
            fields: [$field],
            keys: [$key],
            relations: [$relation]
        );

        $kernel = new ZeusKernel(
            $provider,
            $this->entityRegistry,
            $this->fieldRegistry,
            $this->businessKeyRegistry,
            $this->relationRegistry
        );

        $this->assertFalse($kernel->isBooted());

        $kernel->boot();

        $this->assertTrue($kernel->isBooted());

        // Verify elements were registered correctly
        $this->assertSame($entity, $this->entityRegistry->get('sales_order'));
        $this->assertSame($field, $this->fieldRegistry->get('uuid-field'));
        $this->assertSame($key, $this->businessKeyRegistry->get('uuid-key'));
        $this->assertSame($relation, $this->relationRegistry->get('uuid-relation'));
    }

    /**
     * Test that booting the kernel a second time throws a RuntimeException.
     */
    public function test_it_cannot_boot_more_than_once(): void
    {
        $provider = $this->createMockProvider();
        $kernel = new ZeusKernel(
            $provider,
            $this->entityRegistry,
            $this->fieldRegistry,
            $this->businessKeyRegistry,
            $this->relationRegistry
        );

        $kernel->boot();
        $this->assertTrue($kernel->isBooted());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Zeus Kernel has already been booted.');
        $kernel->boot();
    }
}
