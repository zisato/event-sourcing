<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStream;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot
 */
class AggregateRootRepositoryWithSnapshotTest extends TestCase
{
    private AggregateRootRepositoryInterface|MockObject $aggregateRootRepository;
    private EventStoreInterface|MockObject $eventStore;
    private SnapshotterInterface|MockObject $snapshotter;
    private AggregateRootRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->aggregateRootRepository = $this->createMock(AggregateRootRepositoryInterface::class);
        $this->eventStore = $this->createMock(EventStoreInterface::class);
        $this->snapshotter = $this->createMock(SnapshotterInterface::class);

        $this->repository = new AggregateRootRepositoryWithSnapshot(
            $this->aggregateRootRepository,
            $this->eventStore,
            $this->snapshotter
        );
    }

    public function testItShouldGetFromSnapshotSuccessfully(): void
    {
        $aggregateId = UUID::fromString('41409c26-f4ce-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $eventPersonCreated = $event->withAggregateVersion(1);

        $eventStream1 = EventStream::create();
        $eventStream1->add($eventPersonCreated);

        $event = EventStub::occur($aggregateId->value());
        $event = $event->withAggregateVersion(2);

        $eventStream2 = EventStream::create();
        $eventStream2->add($event);
        
        $aggregate = AggregateRootStub::reconstitute($aggregateId, $eventStream1);

        $expectedResult = AggregateRootStub::reconstitute($aggregateId, $eventStream1);
        $expectedResult->replyEvents($eventStream2);

        $this->snapshotter->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId))
            ->willReturn($aggregate);

        $this->eventStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId->value()))
            ->willReturn($eventStream2);

        $result = $this->repository->get($aggregateId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testItShouldGetFromRepositorySuccessfully(): void
    {
        $aggregateId = UUID::fromString('41409c26-f4ce-11ec-b939-0242ac120002');

        $event1 = EventStub::occur($aggregateId->value());
        $event1 = $event1->withAggregateVersion(1);
        
        $event2 = EventStub::occur($aggregateId->value());
        $event2 = $event2->withAggregateVersion(2);

        $eventStream = EventStream::create();
        $eventStream->add($event1);
        $eventStream->add($event2);

        $expectedResult = AggregateRootStub::reconstitute($aggregateId, $eventStream);

        $this->snapshotter->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId))
            ->willReturn(null);

        $this->aggregateRootRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId))
            ->willReturn($expectedResult);

        $result = $this->repository->get($aggregateId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testItShouldSaveSnapshotSuccessfully(): void
    {
        $aggregateId = UUID::fromString('41409c26-f4ce-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->aggregateRootRepository->expects($this->exactly(1))
            ->method('save');

        $this->snapshotter->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($aggregate));

        $this->repository->save($aggregate);
    }
}
