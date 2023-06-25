### Snapshot

An `Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot` is also provided. This implements `Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository`. It has the following constructor dependencies:

- `Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository` *Required
- `Zisato\EventSourcing\Aggregate\Event\Store\EventStore` *Required
- `Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter` *Required

```php
<?php

use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot;

...

$repository = new AggregateRootRepositoryWithSnapshot(
    $aggregateRootRepository,
    $eventStore,
    $snapshotter
);

```

The `get` method will use `Snapshotter` to return last aggregate from snapshot if exists and replay new events created after the snapshot from `EventStore`

The `save` method use `Snapshotter` to persist the snapshot.

You can use the provided snapshotter `Zisato\EventSourcing\Aggregate\Snapshot\GenericSnapshotter` or create your own implementing `Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter`. It has the following constructor dependencies:

- `Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStore` *Required
- `Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategy` *Required
- `Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotService` *Required

You can use the current DBAL snapshot store implementation from `zisato/event-sourcing-snapshot-store-dbal` or create your own implementing `Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStore`

You can use the provided snapshot strategy `Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy` or create your own implementing `Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategy`

You can use the provided snapshot synchronous service `Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService` or create your own implementing `Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotService`