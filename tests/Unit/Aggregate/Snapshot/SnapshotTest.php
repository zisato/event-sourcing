<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Snapshot;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshot;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Snapshot\Snapshot
 */
class SnapshotTest extends TestCase
{
    public function testItShouldCreateSucessfully(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);
        $aggregateRootVersion = Version::create(1);
        $createdAt = new \DateTimeImmutable();
        $snapshot = Snapshot::create($aggregateRoot, $createdAt);

        $this->assertEquals($snapshot->aggregateRoot(), $aggregateRoot);
        $this->assertEquals($snapshot->aggregateRootId(), $aggregateId);
        $this->assertEquals($snapshot->aggregateRootVersion(), $aggregateRootVersion);
        $this->assertEquals($snapshot->aggregateRootClassName(), AggregateRootStub::class);
        $this->assertEquals($snapshot->createdAt(), $createdAt);
    }
}
