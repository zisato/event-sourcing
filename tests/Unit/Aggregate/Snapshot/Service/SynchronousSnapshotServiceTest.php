<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Snapshot\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshot;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService
 */
class SynchronousSnapshotServiceTest extends TestCase
{
    public function testItShouldCreateSucessfully(): void
    {
        /** @var SnapshotStoreInterface|MockObject $snapshoStore */
        $snapshoStore = $this->createMock(SnapshotStoreInterface::class);
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);
        $snapshot = Snapshot::create($aggregateRoot, new \DateTimeImmutable());

        $service = new SynchronousSnapshotService($snapshoStore);

        $snapshoStore->expects($this->once())
            ->method('save')
            ->with($this->equalTo($snapshot));

        $service->create($snapshot);
    }
}
