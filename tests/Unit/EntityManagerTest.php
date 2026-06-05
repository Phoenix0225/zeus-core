<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Context\TenantContext;
use Zeus\Core\Context\TenantEnforcer;
use Zeus\Core\Contracts\EntityStorageInterface;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\EntityManager;
use Zeus\Core\Exceptions\UnknownFieldException;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\FieldMetadata;

class EntityManagerTest extends TestCase
{
    private function createEntity(string $code): EntityMetadata
    {
        return new EntityMetadata(
            id: 1,
            uuid: 'uuid-1',
            code: $code,
            name: ucfirst($code),
            description: null,
            module_code: 'core',
            is_active: true,
            version: 1,
        );
    }

    private function createField(string $columnName): FieldMetadata
    {
        return new FieldMetadata(
            id: 1,
            uuid: 'uuid-f1',
            entity_id: 1,
            table_name: 'test_table',
            column_name: $columnName,
            type: \Zeus\Core\Metadata\Enums\FieldType::STRING,
            label: ucfirst($columnName),
            data_type: 'string',
            length: 255,
            nullable: true,
            is_business_key: false,
            is_system: false,
            version: 1,
        );
    }

    public function test_it_throws_exception_if_payload_contains_unknown_field(): void
    {
        $entity = $this->createEntity('test_entities');
        $field = $this->createField('name');

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);
        $metadataProvider->expects($this->once())
            ->method('getFields')
            ->with('test_entities')
            ->willReturn([$field]);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $storage = $this->createMock(EntityStorageInterface::class);

        $manager = new EntityManager($metadataProvider, $enforcer, $resolver, $storage);

        $payload = ['name' => 'Test', 'invalid_col' => 'Hack'];

        $this->expectException(UnknownFieldException::class);

        $manager->create($entity, $payload);
    }

    public function test_it_validates_enriches_and_delegates_to_storage(): void
    {
        $entity = $this->createEntity('test_entities');
        $field = $this->createField('name');

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);
        $metadataProvider->expects($this->once())
            ->method('getFields')
            ->with('test_entities')
            ->willReturn([$field]);

        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn(new TenantContext(siteId: 5));

        $enforcer = new TenantEnforcer();

        $storage = $this->createMock(EntityStorageInterface::class);
        $storage->expects($this->once())
            ->method('insert')
            ->with($entity, ['name' => 'Test', 'site_id' => 5])
            ->willReturn(99);

        $manager = new EntityManager($metadataProvider, $enforcer, $resolver, $storage);

        $result = $manager->create($entity, ['name' => 'Test']);

        $this->assertEquals(99, $result);
    }

    public function test_update_throws_exception_if_payload_contains_unknown_field(): void
    {
        $entity = $this->createEntity('test_entities');
        $field = $this->createField('price');

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);
        $metadataProvider->expects($this->once())
            ->method('getFields')
            ->with('test_entities')
            ->willReturn([$field]);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $storage = $this->createMock(EntityStorageInterface::class);

        $manager = new EntityManager($metadataProvider, $enforcer, $resolver, $storage);

        $payload = ['price' => 100, 'invalid_col' => 'Hack'];

        $this->expectException(UnknownFieldException::class);

        $manager->update($entity, 1, $payload);
    }

    public function test_update_validates_and_delegates_to_storage_with_tenant_criteria(): void
    {
        $entity = $this->createEntity('test_entities');
        $field = $this->createField('price');

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);
        $metadataProvider->expects($this->once())
            ->method('getFields')
            ->with('test_entities')
            ->willReturn([$field]);

        $context = new TenantContext();
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn($context);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $criteria = [['field' => 'site_id', 'value' => 5, 'allow_null' => true]];
        $enforcer->expects($this->once())
            ->method('getReadCriteria')
            ->with($context)
            ->willReturn($criteria);

        $storage = $this->createMock(EntityStorageInterface::class);
        $storage->expects($this->once())
            ->method('update')
            ->with($entity, 99, ['price' => 100], $criteria)
            ->willReturn(true);

        $manager = new EntityManager($metadataProvider, $enforcer, $resolver, $storage);

        $result = $manager->update($entity, 99, ['price' => 100]);

        $this->assertTrue($result);
    }

    public function test_delete_delegates_to_storage_with_tenant_criteria(): void
    {
        $entity = $this->createEntity('test_entities');

        $metadataProvider = $this->createMock(MetadataProviderInterface::class);

        $context = new TenantContext();
        $resolver = $this->createMock(TenantContextResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn($context);

        $enforcer = $this->createMock(TenantEnforcer::class);
        $criteria = [['field' => 'site_id', 'value' => 5, 'allow_null' => true]];
        $enforcer->expects($this->once())
            ->method('getReadCriteria')
            ->with($context)
            ->willReturn($criteria);

        $storage = $this->createMock(EntityStorageInterface::class);
        $storage->expects($this->once())
            ->method('delete')
            ->with($entity, 99, $criteria)
            ->willReturn(true);

        $manager = new EntityManager($metadataProvider, $enforcer, $resolver, $storage);

        $result = $manager->delete($entity, 99);

        $this->assertTrue($result);
    }
}
