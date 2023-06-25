<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Repository;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

class AggregateRootRepositoryWithSnapshot implements AggregateRootRepositoryInterface
{
    private AggregateRootRepositoryInterface $aggregateRootRepository;

    private EventStoreInterface $eventStore;

    private SnapshotterInterface $snapshotter;

    public function __construct(
        AggregateRootRepositoryInterface $aggregateRootRepository,
        EventStoreInterface $eventStore,
        SnapshotterInterface $snapshotter
    ) {
        $this->aggregateRootRepository = $aggregateRootRepository;
        $this->eventStore = $eventStore;
        $this->snapshotter = $snapshotter;
    }

    public function get(IdentityInterface $aggregateId): AggregateRootInterface
    {
        $aggregateRoot = $this->snapshotter->get($aggregateId);

        if ($aggregateRoot !== null) {
            $eventStream = $this->eventStore->get($aggregateId->value(), $aggregateRoot->version() ->value());

            $aggregateRoot->replyEvents($eventStream);

            return $aggregateRoot;
        }

        return $this->aggregateRootRepository->get($aggregateId);
    }

    public function save(AggregateRootInterface $aggregateRoot): void
    {
        $this->aggregateRootRepository->save($aggregateRoot);

        $this->snapshotter->handle($aggregateRoot);
    }
}
