<?php

declare(strict_types=1);

namespace Zeus\Core\Tests\Unit\Engine\Event;

use PHPUnit\Framework\TestCase;
use Zeus\Core\Engine\Event\EntityEvent;

class EntityEventTest extends TestCase
{
    public function test_it_instantiates_and_assigns_properties_correctly(): void
    {
        $payload = ['foo' => 'bar'];
        $event = new EntityEvent('entity.created', 'sales_order', $payload);

        $this->assertSame('entity.created', $event->eventName);
        $this->assertSame('sales_order', $event->entityCode);
        $this->assertSame($payload, $event->payload);
    }
}
