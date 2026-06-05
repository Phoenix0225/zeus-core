<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit;

use Error;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\BusinessKeyMetadata;
use Zeus\Core\Metadata\RelationMetadata;
use Zeus\Core\Registry\EntityRegistry;
use Zeus\Core\Registry\FieldRegistry;
use Zeus\Core\Registry\BusinessKeyRegistry;
use Zeus\Core\Registry\RelationRegistry;

/**
 * Class MetadataAndRegistryTest
 *
 * Tests the Phase 1 Metadata classes (hydration, immutability) and
 * Phase 2 Registry classes (registration, logical index retrieval, type constraints).
 */
class MetadataAndRegistryTest extends TestCase
{
    // ==========================================
    // PHASE 1: Metadata Immutability & Hydration
    // ==========================================

    public function test_entity_metadata_hydration_and_immutability(): void
    {
        $entity = new EntityMetadata(
            id: 1,
            uuid: 'e57c1d7e-7b7d-4299-a9a7-96a8e805566f',
            code: 'User',
            name: 'User Account',
            description: 'System user account.',
            module_code: 'auth',
            is_active: true,
            version: 1
        );

        // Verify public read access works
        $this->assertSame(1, $entity->id);
        $this->assertSame('e57c1d7e-7b7d-4299-a9a7-96a8e805566f', $entity->uuid);
        $this->assertSame('User', $entity->code);
        $this->assertSame('User Account', $entity->name);
        $this->assertSame('System user account.', $entity->description);
        $this->assertSame('auth', $entity->module_code);
        $this->assertTrue($entity->is_active);
        $this->assertSame(1, $entity->version);

        // Verify write access is forbidden from the outside
        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $entity->name = 'Modified User Account';
    }

    public function test_field_metadata_hydration_and_immutability(): void
    {
        $field = new FieldMetadata(
            id: 10,
            uuid: 'f89c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 1,
            table_name: 'users',
            column_name: 'email',
            label: 'Email Address',
            data_type: 'string',
            length: 255,
            nullable: false,
            is_business_key: true,
            is_system: false,
            version: 2
        );

        $this->assertSame(10, $field->id);
        $this->assertSame('f89c1d7e-7b7d-4299-a9a7-96a8e805566f', $field->uuid);
        $this->assertSame(1, $field->entity_id);
        $this->assertSame('users', $field->table_name);
        $this->assertSame('email', $field->column_name);
        $this->assertSame('Email Address', $field->label);
        $this->assertSame('string', $field->data_type);
        $this->assertSame(255, $field->length);
        $this->assertFalse($field->nullable);
        $this->assertTrue($field->is_business_key);
        $this->assertFalse($field->is_system);
        $this->assertSame(2, $field->version);

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $field->column_name = 'new_email';
    }

    public function test_business_key_metadata_hydration_and_immutability(): void
    {
        $bk = new BusinessKeyMetadata(
            id: 20,
            uuid: 'b78c1d7e-7b7d-4299-a9a7-96a8e805566f',
            entity_id: 1,
            name: 'UK_user_email',
            is_primary: true,
            version: 1
        );

        $this->assertSame(20, $bk->id);
        $this->assertSame('b78c1d7e-7b7d-4299-a9a7-96a8e805566f', $bk->uuid);
        $this->assertSame(1, $bk->entity_id);
        $this->assertSame('UK_user_email', $bk->name);
        $this->assertTrue($bk->is_primary);
        $this->assertSame(1, $bk->version);

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $bk->is_primary = false;
    }

    public function test_relation_metadata_hydration_and_immutability(): void
    {
        $relation = new RelationMetadata(
            id: 30,
            uuid: 'r12c1d7e-7b7d-4299-a9a7-96a8e805566f',
            source_entity_id: 1,
            target_entity_id: 2,
            relation_type: 'one-to-many',
            source_field: 'id',
            target_field: 'user_id',
            version: 3
        );

        $this->assertSame(30, $relation->id);
        $this->assertSame('r12c1d7e-7b7d-4299-a9a7-96a8e805566f', $relation->uuid);
        $this->assertSame(1, $relation->source_entity_id);
        $this->assertSame(2, $relation->target_entity_id);
        $this->assertSame('one-to-many', $relation->relation_type);
        $this->assertSame('id', $relation->source_field);
        $this->assertSame('user_id', $relation->target_field);
        $this->assertSame(3, $relation->version);

        $this->expectException(Error::class);
        /** @noinspection PhpUnsupportedMemberSignatureInspection */
        $relation->relation_type = 'many-to-many';
    }

    // ==========================================
    // PHASE 2: Registry Functionality & Type Safety
    // ==========================================

    public function test_entity_registry(): void
    {
        $registry = new EntityRegistry();

        $entity1 = new EntityMetadata(1, 'uuid-1', 'User', 'User', null, 'auth', true, 1);
        $entity2 = new EntityMetadata(2, 'uuid-2', 'Tenant', 'Tenant', null, 'core', true, 1);

        $registry->register($entity1);
        $registry->register($entity2);

        // Retrieve by logical code
        $this->assertSame($entity1, $registry->get('User'));
        $this->assertSame($entity2, $registry->get('Tenant'));
        $this->assertNull($registry->get('NonExistent'));

        // Retrieve all
        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($entity1, $all['User']);
        $this->assertSame($entity2, $all['Tenant']);

        // Type safety constraint validation
        $this->expectException(InvalidArgumentException::class);
        $registry->register(new class() {});
    }

    public function test_field_registry(): void
    {
        $registry = new FieldRegistry();

        $field1 = new FieldMetadata(1, 'uuid-field-1', 1, 'users', 'email', 'Email', 'string', 255, false, true, false, 1);
        $field2 = new FieldMetadata(2, 'uuid-field-2', 1, 'users', 'status', 'Status', 'string', 50, false, false, false, 1);

        $registry->register($field1);
        $registry->register($field2);

        // Retrieve by UUID
        $this->assertSame($field1, $registry->get('uuid-field-1'));
        $this->assertSame($field2, $registry->get('uuid-field-2'));
        $this->assertNull($registry->get('uuid-non-existent'));

        // Retrieve all
        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($field1, $all['uuid-field-1']);
        $this->assertSame($field2, $all['uuid-field-2']);

        // Type safety constraint validation
        $this->expectException(InvalidArgumentException::class);
        $registry->register(new class() {});
    }

    public function test_business_key_registry(): void
    {
        $registry = new BusinessKeyRegistry();

        $bk1 = new BusinessKeyMetadata(1, 'uuid-bk-1', 1, 'UK_user_email', true, 1);
        $bk2 = new BusinessKeyMetadata(2, 'uuid-bk-2', 2, 'UK_tenant_code', true, 1);

        $registry->register($bk1);
        $registry->register($bk2);

        // Retrieve by UUID
        $this->assertSame($bk1, $registry->get('uuid-bk-1'));
        $this->assertSame($bk2, $registry->get('uuid-bk-2'));
        $this->assertNull($registry->get('uuid-non-existent'));

        // Retrieve all
        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($bk1, $all['uuid-bk-1']);
        $this->assertSame($bk2, $all['uuid-bk-2']);

        // Type safety constraint validation
        $this->expectException(InvalidArgumentException::class);
        $registry->register(new class() {});
    }

    public function test_relation_registry(): void
    {
        $registry = new RelationRegistry();

        $relation1 = new RelationMetadata(1, 'uuid-relation-1', 1, 2, 'one-to-many', 'id', 'user_id', 1);
        $relation2 = new RelationMetadata(2, 'uuid-relation-2', 2, 3, 'many-to-one', 'tenant_id', 'id', 1);

        $registry->register($relation1);
        $registry->register($relation2);

        // Retrieve by UUID
        $this->assertSame($relation1, $registry->get('uuid-relation-1'));
        $this->assertSame($relation2, $registry->get('uuid-relation-2'));
        $this->assertNull($registry->get('uuid-non-existent'));

        // Retrieve all
        $all = $registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($relation1, $all['uuid-relation-1']);
        $this->assertSame($relation2, $all['uuid-relation-2']);

        // Type safety constraint validation
        $this->expectException(InvalidArgumentException::class);
        $registry->register(new class() {});
    }
}
