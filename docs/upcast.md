### Upcast

Upcasting is provided at event serialization stage in event store with the serializer `Zisato\EventSourcing\Aggregate\Event\Serializer\UpcasterEventSerializer`. It has the following constructor dependencies:

- `Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface`
- `Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface`

The upcaster class will need to implement the following methods:
- `public function canUpcast(Event $event): bool` This will decide if event argument can be upcasted by this class
- `public function upcast(Event $event): Event` This will return the new upcasted event
```php

use Zisato\EventSourcing\Aggregate\Event\Upcast\Upcaster;

class EventFrom1To2Upcaster implements Upcaster
{
    const VERSION_FROM = 1;
    const VERSION_TO = 2;

    public function canUpcast(Event $event): bool
    {
        return $event->version() === self::VERSION_FROM;
    }

    public function upcast(Event $event): Event
    {
        $newPayload = $event->payload();
        $newPayload['upcasted_1_to_2_key'] = 'upcasted_1_to_2_value';

        return Event::reconstitute(
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $newPayload,
            self::VERSION_TO,
            $event->metadata()
        );
    }
}

```

Multiple upcasters can be done using `Zisato\EventSourcing\Aggregate\Event\Upcast\EventUpcasterChain` which implements `UpcasterInterface` and accepts many of `Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface` as arguments

```php

use Zisato\EventSourcing\Aggregate\Aggregate\Event\Upcast\EventUpcasterChain;
use Zisato\EventSourcing\Aggregate\Event\Serializer\UpcasterEventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Upcast\Upcaster;

class Event1From1To2Upcaster implements Upcaster
{
    public function canUpcast(Event $event): bool
    {
        ...
    }

    public function upcast(Event $event): Event
    {
        ...
    }
}

class Event1From2To3Upcaster implements Upcaster
{
    public function canUpcast(Event $event): bool
    {
        ...
    }

    public function upcast(Event $event): Event
    {
        ...
    }
}

class Event2From1To3Upcaster implements Upcaster
{
    public function canUpcast(Event $event): bool
    {
        ...
    }

    public function upcast(Event $event): Event
    {
        ...
    }
}

$eventSerializer = new EventSerializer();

$upcaster1 = new Event1From1To2Upcaster();
$upcaster2 = new Event1From2To3Upcaster();
$upcaster3 = new Event2From1To3Upcaster();
$upcasterChain = new EventUpcasterChain($upcaster1, $upcaster2, $upcaster3);

$serializer = new UpcasterEventSerializer($eventSerializer, $upcasterChain);

// using DBAL event store
$eventStore = new EventStore($connection, $serializer);
```