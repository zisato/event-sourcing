<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Service;

use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;

class SynchronousSnapshotService implements SnapshotServiceInterface
{
    private SnapshotStoreInterface $snapshotStore;

    public function __construct(SnapshotStoreInterface $snapshotStore)
    {
        $this->snapshotStore = $snapshotStore;
    }

    public function create(SnapshotInterface $snapshot): void
    {
        $this->snapshotStore->save($snapshot);
    }
}
