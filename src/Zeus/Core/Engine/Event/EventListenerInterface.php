<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Event;

/**
 * Interface EventListenerInterface
 *
 * Contract for any class that needs to listen and react to EntityEvents.
 */
interface EventListenerInterface
{
    public function handle(EntityEvent $event): void;
}
