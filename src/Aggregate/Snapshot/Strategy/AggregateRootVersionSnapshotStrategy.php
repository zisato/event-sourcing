<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Strategy;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

class AggregateRootVersionSnapshotStrategy implements SnapshotStrategyInterface
{
    private const DEFAULT_VERSION_TO_SNAPSHOT = 20;

    private int $versionToCreateSnapshot;

    public function __construct(int $versionToCreateSnapshot = self::DEFAULT_VERSION_TO_SNAPSHOT)
    {
        $this->versionToCreateSnapshot = $versionToCreateSnapshot;
    }

    public function shouldCreateSnapshot(AggregateRootInterface $aggregateRoot): bool
    {
        return $aggregateRoot->version()
            ->value() % $this->versionToCreateSnapshot === 0;
    }
}
