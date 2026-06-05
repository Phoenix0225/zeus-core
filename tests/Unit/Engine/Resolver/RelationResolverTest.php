<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Resolver;

use InvalidArgumentException;
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
 * Tests incoming and outgoing relation resolution, exception handling, and empty configurations.
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

        // Entity A (Source): sales_order (ID: 10)
        $entityA = new EntityMetadata(
            id: 10,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'sales_order',
            name: 'Sales Order',
            description: null,
            module_code: 'sales',
            is_active: true,
            version: 1
        );
        $this->entityRegistry->register($entityA);

        // Entity B (Target): customer (ID: 20)
        $entityB = new EntityMetadata(
            id: 20,
            uuid: '8a9c8b74-32ef-4b47-ad71-638bc23247c4',
            code: 'customer',
            name: 'Customer',
            description: null,
            module_code: 'crm',
            is_active: true,
            version: 1
        );
        $this->entityRegistry->register($entityB);

        // Source Field: customer_code on sales_order (ID: 100, entity_id: 10)
        $fieldSource = new FieldMetadata(
            id: 100,
            uuid: 'f-so-cust-code',
            entity_id: 10,
            table_name: 'sales_orders',
            column_name: 'customer_code',
            label: 'Customer Code',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: false,
            is_system: false,
            version: 1
        );
        $this->fieldRegistry->register($fieldSource);

        // Target Field: code on customer (ID: 200, entity_id: 20)
        $fieldTarget = new FieldMetadata(
            id: 200,
            uuid: 'f-cust-code',
            entity_id: 20,
            table_name: 'customers',
            column_name: 'code',
            label: 'Code',
            data_type: 'string',
            length: 50,
            nullable: false,
            is_business_key: true,
            is_system: false,
            version: 1
        );
        $this->fieldRegistry->register($fieldTarget);

        // Relation: source_entity_id (10), target_entity_id (20), N:1
        $relation = new RelationMetadata(
            id: 301,
            uuid: 'r-so-to-cust',
            source_entity_id: 10,
            target_entity_id: 20,
            relation_type: 'N:1',
            source_field: 'customer_code',
            target_field: 'code',
            version: 1
        );
        $this->relationRegistry->register($relation);
    }

    /**
     * Valide que l'InvalidArgumentException est lancée si on cherche les relations d'un code entité inconnu.
     */
    public function test_it_throws_exception_if_entity_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity with code "unknown_entity" is not registered.');

        $this->resolver->getOutgoingRelations('unknown_entity');
    }

    /**
     * Appelle getOutgoingRelations('sales_order'). Vérifie qu'il retourne un tableau contenant 1 ResolvedRelation,
     * et valide que les objets internes (source/cible) correspondent exactement à ce qui est attendu.
     */
    public function test_it_resolves_outgoing_relations_correctly(): void
    {
        $relations = $this->resolver->getOutgoingRelations('sales_order');

        $this->assertCount(1, $relations);
        $resolved = $relations[0];

        $this->assertSame(10, $resolved->sourceEntity->id);
        $this->assertSame('sales_order', $resolved->sourceEntity->code);

        $this->assertSame(20, $resolved->targetEntity->id);
        $this->assertSame('customer', $resolved->targetEntity->code);

        $this->assertSame(100, $resolved->sourceField->id);
        $this->assertSame('customer_code', $resolved->sourceField->column_name);

        $this->assertSame(200, $resolved->targetField->id);
        $this->assertSame('code', $resolved->targetField->column_name);

        $this->assertSame('N:1', $resolved->relationType);
    }

    /**
     * Appelle getIncomingRelations('customer'). Vérifie qu'il retourne bien la relation inverse provenant de 'sales_order'.
     */
    public function test_it_resolves_incoming_relations_correctly(): void
    {
        $relations = $this->resolver->getIncomingRelations('customer');

        $this->assertCount(1, $relations);
        $resolved = $relations[0];

        $this->assertSame(10, $resolved->sourceEntity->id);
        $this->assertSame('sales_order', $resolved->sourceEntity->code);

        $this->assertSame(20, $resolved->targetEntity->id);
        $this->assertSame('customer', $resolved->targetEntity->code);

        $this->assertSame(100, $resolved->sourceField->id);
        $this->assertSame('customer_code', $resolved->sourceField->column_name);

        $this->assertSame(200, $resolved->targetField->id);
        $this->assertSame('code', $resolved->targetField->column_name);

        $this->assertSame('N:1', $resolved->relationType);
    }

    /**
     * Crée une 3ème entité isolée et vérifie que la méthode retourne bien un [] vide, sans crasher.
     */
    public function test_it_returns_empty_array_if_no_relations_exist(): void
    {
        // Register an isolated entity
        $isolated = new EntityMetadata(30, 'uuid-isolated', 'isolated_entity', 'Isolated', null, 'core', true, 1);
        $this->entityRegistry->register($isolated);

        $outgoing = $this->resolver->getOutgoingRelations('isolated_entity');
        $incoming = $this->resolver->getIncomingRelations('isolated_entity');

        $this->assertEmpty($outgoing);
        $this->assertEmpty($incoming);
    }
}
