<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Service;

use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface;

interface SnapshotServiceInterface
{
    public function create(SnapshotInterface $snapshot): void;
}
