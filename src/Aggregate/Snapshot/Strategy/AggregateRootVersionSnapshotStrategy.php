<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot\Strategy;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

final class AggregateRootVersionSnapshotStrategy implements SnapshotStrategyInterface
{
    private const DEFAULT_VERSION_TO_SNAPSHOT = 20;

    public function __construct(
        private readonly int $versionToCreateSnapshot = self::DEFAULT_VERSION_TO_SNAPSHOT
    ) {
    }

    public function shouldCreateSnapshot(AggregateRootInterface $aggregateRoot): bool
    {
        return $aggregateRoot->version()
            ->value() % $this->versionToCreateSnapshot === 0;
    }
}
