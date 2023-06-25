### Aggregate root

Aggregate root should implements `Zisato\EventSourcing\Aggregate\AggregateRootInterface` or your can extends from class `Zisato\EventSourcing\Aggregate\AbstractAggregateRoot`. Constructor is final, forcing to create static named constructors

```php
use Zisato\EventSourcing\Aggregate\AbstractAggregateRoot;
use Zisato\EventSourcing\Identity\IdentityInterface;

class MyAggregate extends AbstractAggregateRoot
{
    private string $myProperty;

    public static function create(IdentityInterface $id, string $myProperty): MyAggregate
    {
        $instance = new static($id);

        $instance->recordThat(
            MyAggregateCreated::occur(
                $id->value(),
                [
                    'my_property' => $myProperty,
                ]
            )
            // or
            MyAggregateCreated::create($id, $myProperty)
        );

        return $instance;
    }

    public function changeMyProperty(string $newMyProperty): void
    {
        if ($this->myProperty !== $newMyProperty) {
            $this->recordThat(
                MyAggregateEvent::occur(
                    $this->id()->value(),
                    [
                        'previous_my_property' => $this->myProperty,
                        'new_my_property' => $newMyProperty,
                    ]
                )
                // or
                MyAggregateEvent::create(
                    $this->id(),
                    $this->myProperty,
                    $newMyProperty
                )
            );
        }
    }

    protected function applyMyAggregateCreated(MyAggregateCreated $event): void
    {
        $this->myProperty = $event->payload()['my_property'];
        // or
        $this->myProperty = $event->myProperty();
    }

    protected function applyMyAggregateEvent(MyAggregateEvent $event): void
    {
        $this->myProperty = $event->payload()['new_my_property'];
        // or
        $this->myProperty = $event->myProperty();
    }
}
```

### Aggregate event

Aggregate events should implements `Zisato\EventSourcing\Aggregate\Event\EventInterface` or you can extends from class `Zisato\EventSourcing\Aggregate\Event\AbstractEvent`. Constructor is final, forcing to create static named constructors and taking advantage of payload type hinting, although you could use constructor to instance your aggregate event, it is recommended to use `occur` method.


```php
use Zisato\EventSourcing\Aggregate\Event\AbstractEvent;
use Zisato\EventSourcing\Identity\IdentityInterface;

class MyAggregateEvent extends AbstractEvent
{
    public static function create(IdentityInterface $aggregateId, string $myProperty): MyAggregateEvent
    {
        return static::occur(
            $aggregateId->value(),
            [
                'my_property' => $myProperty,
            ]
        );
    }

    public function myProperty(): string
    {
        return $this->payload()['my_property'];
    }
}
```

### Aggregate event version
All `AbstractEvent` are created with version 1 by default and version is resolved at event serialization stage in event store with the class `Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface`

You can use the provided version resolver  `Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver` which will look for a public static method `defaultVersion` in event class or create your own implementing `Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface`


```php
use Zisato\EventSourcing\Aggregate\Event\AbstractEvent;
use Zisato\EventSourcing\Identity\IdentityInterface;

class MyAggregateVersionedEvent extends AbstractEvent
{
    private const DEFAULT_VERSION = 2;

    protected static function defaultVersion(): int
    {
        return static::DEFAULT_VERSION;
    }

    public static function create(IdentityInterface $aggregateId, string $myProperty): MyAggregateVersionedEvent
    {
        return static::occur(
            $aggregateId->value(),
            [
                'my_property' => $myProperty,
            ]
        );
    }

    public function myProperty(): string
    {
        return $this->data()['my_property'];
    }
}
```