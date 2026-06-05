<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Event;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Event\EntityEvent;
use Zeus\Core\Engine\Event\EventDispatcher;
use Zeus\Core\Engine\Event\EventListenerInterface;

class DummyListener implements EventListenerInterface
{
    /**
     * @var array<EntityEvent>
     */
    public array $handledEvents = [];

    public function handle(EntityEvent $event): void
    {
        $this->handledEvents[] = $event;
    }
}

class EventDispatcherTest extends TestCase
{
    public function test_it_can_subscribe_and_dispatch_an_event(): void
    {
        $dispatcher = new EventDispatcher();
        $listener = new DummyListener();

        $dispatcher->subscribe('entity.created', $listener);

        $event = new EntityEvent('entity.created', 'sales_order', ['id' => 123]);
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listener->handledEvents);
        $this->assertSame($event, $listener->handledEvents[0]);
    }

    public function test_it_can_handle_multiple_listeners_for_same_event(): void
    {
        $dispatcher = new EventDispatcher();
        $listenerA = new DummyListener();
        $listenerB = new DummyListener();

        $dispatcher->subscribe('entity.created', $listenerA);
        $dispatcher->subscribe('entity.created', $listenerB);

        $event = new EntityEvent('entity.created', 'sales_order');
        $dispatcher->dispatch($event);

        $this->assertCount(1, $listenerA->handledEvents);
        $this->assertCount(1, $listenerB->handledEvents);
        $this->assertSame($event, $listenerA->handledEvents[0]);
        $this->assertSame($event, $listenerB->handledEvents[0]);
    }

    public function test_it_does_not_dispatch_to_unrelated_listeners(): void
    {
        $dispatcher = new EventDispatcher();
        $listener = new DummyListener();

        $dispatcher->subscribe('entity.updated', $listener);

        $event = new EntityEvent('entity.created', 'sales_order');
        $dispatcher->dispatch($event);

        $this->assertEmpty($listener->handledEvents);
    }

    public function test_it_does_not_crash_if_no_listeners_are_subscribed(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new EntityEvent('unknown.event', 'sales_order');

        // This should run without throwing any exceptions or errors
        $dispatcher->dispatch($event);

        $this->assertTrue(true); // Ensures the test is considered passing and not risky
    }
}
