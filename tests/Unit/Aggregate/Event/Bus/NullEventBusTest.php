<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Bus;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Bus\NullEventBus;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Bus\NullEventBus
 */
class NullEventBusTest extends TestCase
{
    public function testHandle(): void
    {
        $eventBus = new NullEventBus();

        $event = $this->createMock(EventInterface::class);

        $event->expects($this->never())
            ->method('occur');

        $event->expects($this->never())
            ->method('reconstitute');

        $event->expects($this->never())
            ->method('withMetadata');

        $event->expects($this->never())
            ->method('withAggregateVersion');

        $event->expects($this->never())
            ->method('aggregateId');

        $event->expects($this->never())
            ->method('aggregateVersion');

        $event->expects($this->never())
            ->method('payload');

        $event->expects($this->never())
            ->method('createdAt');

        $event->expects($this->never())
            ->method('version');

        $event->expects($this->never())
            ->method('metadata');

        $eventBus->handle($event);
    }
}
