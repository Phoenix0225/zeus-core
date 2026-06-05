<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Manager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zeus\Core\Contracts\StorageInterface;
use Zeus\Core\Engine\Event\EntityEvent;
use Zeus\Core\Engine\Event\EventDispatcher;
use Zeus\Core\Engine\Exception\ValidationException;
use Zeus\Core\Engine\Manager\EntityManager;
use Zeus\Core\Engine\Validator\EntityValidator;
use Zeus\Core\Engine\Validator\ValidationResult;

class EntityManagerTest extends TestCase
{
    private EntityValidator&MockObject $validatorMock;
    private StorageInterface&MockObject $storageMock;
    private EventDispatcher&MockObject $dispatcherMock;
    private EntityManager $manager;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(EntityValidator::class);
        $this->storageMock = $this->createMock(StorageInterface::class);
        $this->dispatcherMock = $this->createMock(EventDispatcher::class);

        $this->manager = new EntityManager(
            $this->validatorMock,
            $this->storageMock,
            $this->dispatcherMock
        );
    }

    public function test_it_can_create_an_entity_when_validation_passes(): void
    {
        $entityCode = 'sales_order';
        $data = ['total_amount' => 150];

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($entityCode, $data)
            ->willReturn(new ValidationResult(true, []));

        $this->storageMock
            ->expects($this->once())
            ->method('insert')
            ->with($entityCode, $data)
            ->willReturn(123);

        $this->dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (EntityEvent $event) use ($entityCode, $data) {
                return $event->eventName === 'entity.created'
                    && $event->entityCode === $entityCode
                    && $event->payload['id'] === 123
                    && $event->payload['data'] === $data;
            }));

        $id = $this->manager->create($entityCode, $data);

        $this->assertSame(123, $id);
    }

    public function test_it_throws_exception_on_create_when_validation_fails(): void
    {
        $entityCode = 'sales_order';
        $data = ['total_amount' => 'invalid'];
        $result = new ValidationResult(false, ['total_amount' => ['Invalid type']]);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($entityCode, $data)
            ->willReturn($result);

        $this->storageMock
            ->expects($this->never())
            ->method('insert');

        $this->dispatcherMock
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(ValidationException::class);

        $this->manager->create($entityCode, $data);
    }

    public function test_it_can_update_an_entity(): void
    {
        $entityCode = 'sales_order';
        $id = 123;
        $data = ['total_amount' => 200];

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($entityCode, $data)
            ->willReturn(new ValidationResult(true, []));

        $this->storageMock
            ->expects($this->once())
            ->method('update')
            ->with($entityCode, $id, $data)
            ->willReturn(true);

        $this->dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (EntityEvent $event) use ($entityCode, $id, $data) {
                return $event->eventName === 'entity.updated'
                    && $event->entityCode === $entityCode
                    && $event->payload['id'] === $id
                    && $event->payload['data'] === $data;
            }));

        $success = $this->manager->update($entityCode, $id, $data);

        $this->assertTrue($success);
    }

    public function test_it_can_delete_an_entity(): void
    {
        $entityCode = 'sales_order';
        $id = 123;

        $this->storageMock
            ->expects($this->once())
            ->method('delete')
            ->with($entityCode, $id)
            ->willReturn(true);

        $this->dispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (EntityEvent $event) use ($entityCode, $id) {
                return $event->eventName === 'entity.deleted'
                    && $event->entityCode === $entityCode
                    && $event->payload['id'] === $id;
            }));

        $success = $this->manager->delete($entityCode, $id);

        $this->assertTrue($success);
    }
}
