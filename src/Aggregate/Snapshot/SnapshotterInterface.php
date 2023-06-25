<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

interface SnapshotterInterface
{
    public function get(IdentityInterface $aggregateId): ?AggregateRootInterface;

    public function handle(AggregateRootInterface $aggregateRoot): void;
}
