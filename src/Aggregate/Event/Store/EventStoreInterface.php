<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Store;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStreamInterface;

interface EventStoreInterface
{
    public function exists(string $aggregateId): bool;

    public function get(string $aggregateId, int $fromAggregateVersion): EventStreamInterface;

    public function append(EventInterface $event): void;
}
