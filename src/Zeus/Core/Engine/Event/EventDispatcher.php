<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Event;

/**
 * Class EventDispatcher
 *
 * The central service responsible for maintaining subscriptions and dispatching events to listeners.
 */
class EventDispatcher
{
    /**
     * @var array<string, array<EventListenerInterface>>
     */
    private array $listeners = [];

    /**
     * Subscribes a listener to a specific event name.
     *
     * @param string $eventName
     * @param EventListenerInterface $listener
     */
    public function subscribe(string $eventName, EventListenerInterface $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatches an event to all subscribed listeners.
     *
     * @param EntityEvent $event
     */
    public function dispatch(EntityEvent $event): void
    {
        $eventName = $event->eventName;

        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            $listener->handle($event);
        }
    }
}
