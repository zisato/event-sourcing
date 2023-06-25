<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Stream;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStream;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Stream\EventStream
 */
class EventStreamTest extends TestCase
{
    /**
     * @dataProvider getAddData
     */
    public function testAdd(array $events): void
    {
        $eventStream = EventStream::create();

        foreach ($events as $event) {
            $eventStream->add($event);
        }

        $expectedCount = \count($events);
        $index = 0;

        $this->assertEquals($expectedCount, $eventStream->count());

        foreach ($eventStream->events() as $event) {
            $this->assertEquals($events[$index], $event);

            $index++;
        }
    }

    /**
     * @dataProvider getIsEmptyData
     */
    public function testIsEmpty(bool $expected, array $events): void
    {
        $eventStream = EventStream::create();

        foreach ($events as $event) {
            $eventStream->add($event);
        }

        $this->assertEquals($expected, $eventStream->isEmpty());
    }

    public static function getAddData(): array
    {
        $aggregateId = '2c2f0530-f3fb-11ec-b939-0242ac120002';

        return [
            [
                []
            ],
            [
                [
                    EventStub::occur($aggregateId),
                ]
            ],
            [
                [
                    EventStub::occur($aggregateId),
                    EventStub::occur($aggregateId),
                ]
            ],
            [
                [
                    EventStub::occur($aggregateId),
                    EventStub::occur($aggregateId),
                    EventStub::occur($aggregateId),
                ]
            ],
        ];
    }

    public static function getIsEmptyData(): array
    {
        return [
            [
                true,
                []
            ],
            [
                false,
                [
                    EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002'),
                ]
            ]
        ];
    }
}
