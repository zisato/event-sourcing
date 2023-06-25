### Aggregate root repository

Aggregate root repository should implements `Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryInterface` or your can use the class `Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository`. It has the following constructor dependencies:

- `aggregate root class name` *Required
- `Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface` *Required
- `Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface`
- `Zisato\EventSourcing\Aggregate\Event\Bus\EventBusInterface`

```php
<?php

use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository;

...

$repository = new AggregateRootRepository(
    MyAggregate::class,
    $eventStore
);

$repository = new AggregateRootRepository(
    MyAggregate::class,
    $eventStore,
    $eventDecorator
);

$repository = new AggregateRootRepository(
    MyAggregate::class,
    $eventStore,
    $eventDecorator,
    $eventBus
);
```

The `save` method will release the aggregate events, decorate them if decorator is provided, persist in event store and
send to the event bus if provided.

For EventStore you can use the current DBAL event store implementation from `zisato/event-sourcing-event-store-dbal` or create your own implementing `Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface`

### Decorate events
Create class implementing `Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface`. If want to have many decorators you can use `Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain` which implements `EventDecoratorInterface` and accepts many of `Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface` as arguments

```php
<?php

use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain;

...

$eventDecoratorChain = new EventDecoratorChain(
    new FooEventDecorator(),
    new BarEventDecorator()
);

...

$repository = new AggregateRootRepository(
    MyAggregate::class,
    $eventStore,
    $eventDecoratorChain
);

```