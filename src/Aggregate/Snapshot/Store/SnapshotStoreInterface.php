<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Store;

use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

interface SnapshotStoreInterface
{
    public function get(IdentityInterface $aggregateId): ?SnapshotInterface;

    public function save(SnapshotInterface $snapshot): void;
}
