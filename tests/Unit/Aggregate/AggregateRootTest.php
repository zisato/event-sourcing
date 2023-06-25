<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStream;
use Zisato\EventSourcing\Aggregate\Exception\AggregateReconstituteException;
use Zisato\EventSourcing\Aggregate\Exception\InvalidAggregateVersionException;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\AbstractAggregateRoot
 */
class AggregateRootTest extends TestCase
{
    public function testReconstitute(): void
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $eventStream = $aggregate->releaseRecordedEvents();

        $expected = $aggregate::reconstitute($aggregateId, $eventStream);

        $this->assertEquals($expected, $aggregate);
        $this->assertEquals($expected->id(), $aggregateId);
        $this->assertEquals($expected->version(), $aggregate->version());
    }

    /**
     * @dataProvider getHasRecordedEventsData
     */
    public function testHasRecordedEvents(bool $expected, AggregateRootInterface $aggregateRoot): void
    {
        $this->assertEquals($expected, $aggregateRoot->hasRecordedEvents());
    }

    public function testExceptionWhenEmptyEventStream(): void
    {
        $this->expectException(AggregateReconstituteException::class);
        $this->expectExceptionMessage('Cannot reconstitute aggregate from empty event stream');

        $aggregateId = UUID::generate();
        $eventStream = EventStream::create();

        AggregateRootStub::reconstitute($aggregateId, $eventStream);
    }

    public function testExceptionWhenApplyEventAggregateVersionLowerThanAggregateVersion(): void
    {
        $this->expectException(InvalidAggregateVersionException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot apply event %s with aggregate version %d, it must follow current aggregate version %d',
                EventStub::class,
                0,
                1
            )
        );

        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');
        $eventStream = EventStream::create();
        $event = EventStub::occur($aggregateId->value());

        $eventStream->add($event);

        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $aggregate->replyEvents($eventStream);
    }

    public function testThrowExceptionWhenApplyEventAggregateVersionGreaterThanNextAggregateVersion(): void
    {
        $this->expectException(InvalidAggregateVersionException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Cannot apply event %s with aggregate version %d, it must follow current aggregate version %d',
                EventStub::class,
                42,
                1
            )
        );

        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');
        $eventStream = EventStream::create();
        $event = EventStub::occur($aggregateId->value());

        $newEvent = $event->withAggregateVersion(42);
        $eventStream->add($newEvent);

        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $aggregate->replyEvents($eventStream);
    }

    public static function getHasRecordedEventsData(): array
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');
        $emptyEventsAggregateRoot = AggregateRootStub::fromEvent($aggregateId, EventStub::occur($aggregateId->value()));
        $emptyEventsAggregateRoot->releaseRecordedEvents();

        return [
            [
                true,
                AggregateRootStub::fromEvent($aggregateId, EventStub::occur($aggregateId->value()))
            ],
            [
                false,
                $emptyEventsAggregateRoot
            ],
            [
                false,
                AggregateRootStub::fromIdentity($aggregateId)
            ]
        ];
    }
}
