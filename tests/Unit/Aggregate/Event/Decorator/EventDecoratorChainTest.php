<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Decorator;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Decorator\EventStubBarDecorator;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Decorator\EventStubFooDecorator;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain
 */
class EventDecoratorChainTest extends TestCase
{
    /**
     * @dataProvider getDecorateData
     */
    public function testDecorate(EventInterface $event, EventInterface $expectedEvent, array $decorators): void
    {
        $eventDecorator = new EventDecoratorChain(...$decorators);

        $result = $eventDecorator->decorate($event);

        $this->assertEquals($result, $expectedEvent);
    }

    public static function getDecorateData(): array
    {
        $aggregateId = '2c2f0530-f3fb-11ec-b939-0242ac120002';
        $event = EventStub::occur($aggregateId);

        return [
            [
                $event,
                $event,
                []
            ],
            [
                $event,
                $event->withMetadata('foo', 'foo'),
                [
                    new EventStubFooDecorator()
                ]
            ],
            [
                $event,
                $event->withMetadata('foo', 'foo')->withMetadata('bar', 'bar'),
                [
                    new EventStubFooDecorator(),
                    new EventStubBarDecorator()
                ]
            ]
        ];
    }
}
