<?php

declare(strict_types=1);

namespace Zeus\Core\Metadata;

use Zeus\Core\Contracts\EventDispatcherInterface;
use Zeus\Core\Contracts\MetadataProviderInterface;
use Zeus\Core\Exceptions\FieldAlreadyExistsException;
use Zeus\Core\Exceptions\FieldNotFoundException;
use Zeus\Core\Metadata\Events\FieldAddedEvent;
use Zeus\Core\Metadata\Events\FieldDeletedEvent;
use Zeus\Core\Metadata\Events\FieldUpdatedEvent;

class SchemaManager
{
    public function __construct(
        private readonly MetadataProviderInterface $provider,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function addField(EntityMetadata $entity, FieldMetadata $newField): void
    {
        $existingFields = $this->provider->getFields($entity->code);

        foreach ($existingFields as $existingField) {
            if ($existingField->column_name === $newField->column_name) {
                throw new FieldAlreadyExistsException($entity->name, $newField->column_name);
            }
        }

        $this->dispatcher->dispatch(new FieldAddedEvent($entity, $newField));
    }

    public function updateField(EntityMetadata $entity, FieldMetadata $originalField, FieldMetadata $newField): void
    {
        $existingFields = $this->provider->getFields($entity->code);
        $found = false;

        foreach ($existingFields as $existingField) {
            if ($existingField->column_name === $originalField->column_name) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new FieldNotFoundException($entity->name, $originalField->column_name);
        }

        $this->dispatcher->dispatch(new FieldUpdatedEvent($entity, $originalField, $newField));
    }

    public function deleteField(EntityMetadata $entity, FieldMetadata $field): void
    {
        $existingFields = $this->provider->getFields($entity->code);
        $found = false;

        foreach ($existingFields as $existingField) {
            if ($existingField->column_name === $field->column_name) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new FieldNotFoundException($entity->name, $field->column_name);
        }

        $this->dispatcher->dispatch(new FieldDeletedEvent($entity, $field));
    }
}
