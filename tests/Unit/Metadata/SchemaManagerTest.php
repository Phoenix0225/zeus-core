<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Metadata;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Contracts\EventDispatcherInterface;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Exceptions\FieldAlreadyExistsException;
use Zeus\Core\Exceptions\FieldNotFoundException;
use Zeus\Core\Metadata\EntityMetadata;
use Zeus\Core\Metadata\Events\FieldAddedEvent;
use Zeus\Core\Metadata\Events\FieldDeletedEvent;
use Zeus\Core\Metadata\Events\FieldUpdatedEvent;
use Zeus\Core\Metadata\FieldMetadata;
use Zeus\Core\Metadata\SchemaManager;

class SchemaManagerTest extends TestCase
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
            nullable: false,
            is_business_key: false,
            is_system: false,
            version: 1,
        );
    }

    public function test_it_dispatches_event_when_adding_a_new_field(): void
    {
        $entity = $this->createEntity('product');
        $field = $this->createField('serial_number');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FieldAddedEvent::class));

        $manager = new SchemaManager($provider, $dispatcher);
        $manager->addField($entity, $field);
    }

    public function test_it_throws_exception_if_field_already_exists(): void
    {
        $entity = $this->createEntity('product');
        $existingField = $this->createField('price');
        $newField = $this->createField('price');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([$existingField]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
            ->method('dispatch');

        $manager = new SchemaManager($provider, $dispatcher);

        $this->expectException(FieldAlreadyExistsException::class);

        $manager->addField($entity, $newField);
    }

    public function test_it_dispatches_event_when_updating_an_existing_field(): void
    {
        $entity = $this->createEntity('product');
        $originalField = $this->createField('price');
        $newField = $this->createField('price_updated');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([$originalField]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FieldUpdatedEvent::class));

        $manager = new SchemaManager($provider, $dispatcher);
        $manager->updateField($entity, $originalField, $newField);
    }

    public function test_it_throws_exception_if_field_to_update_does_not_exist(): void
    {
        $entity = $this->createEntity('product');
        $originalField = $this->createField('price');
        $newField = $this->createField('price_updated');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
            ->method('dispatch');

        $manager = new SchemaManager($provider, $dispatcher);

        $this->expectException(FieldNotFoundException::class);

        $manager->updateField($entity, $originalField, $newField);
    }

    public function test_it_dispatches_event_when_deleting_an_existing_field(): void
    {
        $entity = $this->createEntity('product');
        $field = $this->createField('price');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([$field]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FieldDeletedEvent::class));

        $manager = new SchemaManager($provider, $dispatcher);
        $manager->deleteField($entity, $field);
    }

    public function test_it_throws_exception_if_field_to_delete_does_not_exist(): void
    {
        $entity = $this->createEntity('product');
        $field = $this->createField('price');

        $provider = $this->createMock(MetadataProviderInterface::class);
        $provider->expects($this->once())
            ->method('getFields')
            ->with('product')
            ->willReturn([]);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
            ->method('dispatch');

        $manager = new SchemaManager($provider, $dispatcher);

        $this->expectException(FieldNotFoundException::class);

        $manager->deleteField($entity, $field);
    }
}
