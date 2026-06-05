<?php

declare(strict_types=1);

namespace Zeus\Core\Engine\Event;

/**
 * Class EntityEvent
 *
 * Immutable DTO that carries the details of an event that occurred in the system.
 */
readonly class EntityEvent
{
    /**
     * @param string $eventName The name of the event (e.g., 'entity.created').
     * @param string $entityCode The code of the associated entity.
     * @param array<string, mixed> $payload The data associated with the event.
     */
    public function __construct(
        public string $eventName,
        public string $entityCode,
        public array $payload = [],
    ) {}
}
