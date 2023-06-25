<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

class Snapshotter implements SnapshotterInterface
{
    private SnapshotStoreInterface $snapshotStore;

    private SnapshotStrategyInterface $snapshotStrategy;

    private SnapshotServiceInterface $snapshotService;

    public function __construct(
        SnapshotStoreInterface $snapshotStore,
        SnapshotStrategyInterface $snapshotStrategy,
        SnapshotServiceInterface $snapshotService
    ) {
        $this->snapshotStore = $snapshotStore;
        $this->snapshotStrategy = $snapshotStrategy;
        $this->snapshotService = $snapshotService;
    }

    public function get(IdentityInterface $aggregateId): ?AggregateRootInterface
    {
        $snapshot = $this->snapshotStore->get($aggregateId);

        if ($snapshot === null) {
            return null;
        }

        return $snapshot->aggregateRoot();
    }

    public function handle(AggregateRootInterface $aggregateRoot): void
    {
        if ($this->snapshotStrategy->shouldCreateSnapshot($aggregateRoot)) {
            $this->snapshotService->create(Snapshot::create($aggregateRoot, new \DateTimeImmutable()));
        }
    }
}
