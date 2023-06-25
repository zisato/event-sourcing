<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Bus\EventBusInterface;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStream;
use Zisato\EventSourcing\Aggregate\Exception\AggregateRootDeletedException;
use Zisato\EventSourcing\Aggregate\Exception\AggregateRootNotFoundException;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootDeletableStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventDeleteStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository
 */
class AggregateRootRepositoryTest extends TestCase
{
    private AggregateRootRepositoryInterface $repository;
    /** @var EventStoreInterface|MockObject $eventStore */
    private $eventStore;

    protected function setUp(): void
    {
        $this->eventStore = $this->createMock(EventStoreInterface::class);

        $this->repository = new AggregateRootRepository(
            AggregateRootStub::class,
            $this->eventStore
        );
    }

    public function testGet(): void
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $event = $event->withAggregateVersion(1);

        $eventStream = EventStream::create();
        $eventStream->add($event);

        $expectedResult = AggregateRootStub::reconstitute($aggregateId, $eventStream);

        $this->eventStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId->value()))
            ->willReturn($eventStream);

        $result = $this->repository->get($aggregateId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testSave(): void
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->eventStore->expects($this->once())
            ->method('append');

        $this->repository->save($aggregate);
    }

    public function testSaveWithEventDecorator(): void
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->eventStore->expects($this->exactly(1))
            ->method('append');

        $eventDecorator = $this->createMock(EventDecoratorInterface::class);
        $eventDecorator->expects($this->exactly(1))
            ->method('decorate');

        $repository = new AggregateRootRepository(
            Person::class,
            $this->eventStore,
            $eventDecorator
        );

        $repository->save($aggregate);
    }

    public function testSaveWithEventBus(): void
    {
        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->eventStore->expects($this->exactly(1))
            ->method('append');

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->exactly(1))
            ->method('handle');

        $repository = new AggregateRootRepository(
            Person::class,
            $this->eventStore,
            null,
            $eventBus
        );

        $repository->save($aggregate);
    }

    public function testThrowAggregateRootNotFoundException(): void
    {
        $this->expectException(AggregateRootNotFoundException::class);

        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');
        $eventStream = EventStream::create();

        $this->eventStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId->value()))
            ->willReturn($eventStream);

        $this->repository->get($aggregateId);
    }

    public function testGetWhenNotDeleted(): void
    {
        $repository = new AggregateRootRepository(
            AggregateRootDeletableStub::class,
            $this->eventStore
        );

        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventStub::occur($aggregateId->value());
        $event = $event->withAggregateVersion(1);

        $eventStream = EventStream::create();
        $eventStream->add($event);

        $expectedResult = AggregateRootDeletableStub::reconstitute($aggregateId, $eventStream);

        $this->eventStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId->value()))
            ->willReturn($eventStream);

        $result = $repository->get($aggregateId);

        $this->assertEquals($expectedResult, $result);
    }

    public function testThrowAggregateRootDeletedException(): void
    {
        $this->expectException(AggregateRootDeletedException::class);

        $repository = new AggregateRootRepository(
            AggregateRootDeletableStub::class,
            $this->eventStore
        );

        $aggregateId = UUID::fromString('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $event = EventDeleteStub::occur($aggregateId->value());
        $event = $event->withAggregateVersion(1);

        $eventStream = EventStream::create();
        $eventStream->add($event);

        AggregateRootDeletableStub::reconstitute($aggregateId, $eventStream);

        $this->eventStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId->value()))
            ->willReturn($eventStream);

        $repository->get($aggregateId);
    }
}
