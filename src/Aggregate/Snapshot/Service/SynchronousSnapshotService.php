<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Service;

use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;

final class SynchronousSnapshotService implements SnapshotServiceInterface
{
    public function __construct(private readonly SnapshotStoreInterface $snapshotStore)
    {
    }

    public function create(SnapshotInterface $snapshot): void
    {
        $this->snapshotStore->save($snapshot);
    }
}
