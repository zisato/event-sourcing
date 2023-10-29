<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Snapshot;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshot;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter
 */
class SnapshotterTest extends TestCase
{
    private SnapshotStoreInterface|MockObject $snapshotStore;
    private SnapshotStrategyInterface|MockObject $snapshotStrategy;
    private SnapshotServiceInterface|MockObject $snapshotService;
    private SnapshotterInterface $snapshotter;

    protected function setUp(): void
    {
        $this->snapshotStore = $this->createMock(SnapshotStoreInterface::class);
        $this->snapshotStrategy = $this->createMock(SnapshotStrategyInterface::class);
        $this->snapshotService = $this->createMock(SnapshotServiceInterface::class);
        
        $this->snapshotter = new Snapshotter(
            $this->snapshotStore,
            $this->snapshotStrategy,
            $this->snapshotService
        );
    }

    /**
     * @dataProvider getGetSuccessData
     */
    public function testItShouldGetSucessfully(
        IdentityInterface $aggregateId,
        ?Snapshot $snapshot,
        ?AggregateRootInterface $expected
    ): void {
        $this->snapshotStore->expects($this->once())
            ->method('get')
            ->with($this->equalTo($aggregateId))
            ->willReturn($snapshot);

        $aggregate = $this->snapshotter->get($aggregateId);

        $this->assertEquals($expected, $aggregate);
    }

    public function testItShoulNotCreateWhenStrategyReturnFalse(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->snapshotStrategy->expects($this->once())
            ->method('shouldCreateSnapshot')
            ->with($this->equalTo($aggregateRoot))
            ->willReturn(false);

        $this->snapshotService->expects($this->never())
            ->method('create');

        $this->snapshotter->handle($aggregateRoot);
    }

    public function testItShoultCreateWhenStrategyReturnTrue(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->snapshotStrategy->expects($this->once())
            ->method('shouldCreateSnapshot')
            ->with($this->equalTo($aggregateRoot))
            ->willReturn(true);

        $this->snapshotService->expects($this->once())
            ->method('create');

        $this->snapshotter->handle($aggregateRoot);
    }

    public static function getGetSuccessData(): array
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');

        return [
            [
                $aggregateId,
                $snapshot = Snapshot::create(
                    AggregateRootStub::fromEvent(
                        $aggregateId,
                        EventStub::occur($aggregateId->value())
                    ), 
                    new \DateTimeImmutable()
                ),
                $snapshot->aggregateRoot(),
            ],
            [
                $aggregateId,
                null,
                null,
            ]
        ];
    }
}
