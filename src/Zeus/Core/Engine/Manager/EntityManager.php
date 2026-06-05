<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Manager;

use Zeus\Core\Contracts\StorageInterface;
use Zeus\Core\Engine\Event\EntityEvent;
use Zeus\Core\Engine\Event\EventDispatcher;
use Zeus\Core\Engine\Exception\ValidationException;
use Zeus\Core\Engine\Validator\EntityValidator;

/**
 * Class EntityManager
 *
 * The central service for performing CRUD operations on entities.
 * Orchestrates validation, storage persistence, and event dispatching.
 */
class EntityManager
{
    /**
     * @param EntityValidator $validator
     * @param StorageInterface $storage
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        private readonly EntityValidator $validator,
        private readonly StorageInterface $storage,
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    /**
     * Validates data, persists a new entity record, and dispatches an event.
     *
     * @param string $entityCode
     * @param array<string, mixed> $data
     * @return int|string
     * @throws ValidationException
     */
    public function create(string $entityCode, array $data): int|string
    {
        $validationResult = $this->validator->validate($entityCode, $data);

        if (!$validationResult->isValid) {
            throw new ValidationException($validationResult);
        }

        $id = $this->storage->insert($entityCode, $data);

        $payload = ['id' => $id, 'data' => $data];
        $this->eventDispatcher->dispatch(new EntityEvent('entity.created', $entityCode, $payload));

        return $id;
    }

    /**
     * Validates data, updates an existing entity record, and dispatches an event.
     *
     * @param string $entityCode
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return bool
     * @throws ValidationException
     */
    public function update(string $entityCode, int|string $id, array $data): bool
    {
        $validationResult = $this->validator->validate($entityCode, $data);

        if (!$validationResult->isValid) {
            throw new ValidationException($validationResult);
        }

        $success = $this->storage->update($entityCode, $id, $data);

        if ($success) {
            $payload = ['id' => $id, 'data' => $data];
            $this->eventDispatcher->dispatch(new EntityEvent('entity.updated', $entityCode, $payload));
        }

        return $success;
    }

    /**
     * Deletes an existing entity record and dispatches an event.
     *
     * @param string $entityCode
     * @param int|string $id
     * @return bool
     */
    public function delete(string $entityCode, int|string $id): bool
    {
        $success = $this->storage->delete($entityCode, $id);

        if ($success) {
            $payload = ['id' => $id];
            $this->eventDispatcher->dispatch(new EntityEvent('entity.deleted', $entityCode, $payload));
        }

        return $success;
    }
}
