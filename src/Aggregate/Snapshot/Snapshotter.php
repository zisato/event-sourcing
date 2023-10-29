<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use DateTimeImmutable;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

final class Snapshotter implements SnapshotterInterface
{
    public function __construct(
        private readonly SnapshotStoreInterface $snapshotStore,
        private readonly SnapshotStrategyInterface $snapshotStrategy,
        private readonly SnapshotServiceInterface $snapshotService
    ) {
    }

    public function get(IdentityInterface $aggregateId): ?AggregateRootInterface
    {
        $snapshot = $this->snapshotStore->get($aggregateId);

        if (! $snapshot instanceof \Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface) {
            return null;
        }

        return $snapshot->aggregateRoot();
    }

    public function handle(AggregateRootInterface $aggregateRoot): void
    {
        if ($this->snapshotStrategy->shouldCreateSnapshot($aggregateRoot)) {
            $this->snapshotService->create(Snapshot::create($aggregateRoot, new DateTimeImmutable()));
        }
    }
}
