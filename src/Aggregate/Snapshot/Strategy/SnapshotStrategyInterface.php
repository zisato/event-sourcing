<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Strategy;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

interface SnapshotStrategyInterface
{
    public function shouldCreateSnapshot(AggregateRootInterface $aggregateRoot): bool;
}
