<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Repository;

use Zisato\EventSourcing\Aggregate\AggregateRootDeletableInterface;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\Bus\EventBusInterface;
use Zisato\EventSourcing\Aggregate\Event\Bus\NullEventBus;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\Decorator\NullEventDecorator;
use Zisato\EventSourcing\Aggregate\Event\Store\EventStoreInterface;
use Zisato\EventSourcing\Aggregate\Exception\AggregateRootDeletedException;
use Zisato\EventSourcing\Aggregate\Exception\AggregateRootNotFoundException;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

final class AggregateRootRepository implements AggregateRootRepositoryInterface
{
    public function __construct(protected string $aggregateRootName, protected EventStoreInterface $eventStore, protected EventDecoratorInterface $eventDecorator = new NullEventDecorator(), protected EventBusInterface $eventBus = new NullEventBus())
    {
    }

    public function get(IdentityInterface $aggregateId): AggregateRootInterface
    {
        $eventStream = $this->eventStore->get($aggregateId->value(), Version::zero()->value());

        if ($eventStream->isEmpty()) {
            throw new AggregateRootNotFoundException(\sprintf(
                'AggregateRoot with id %s not found',
                $aggregateId->value()
            ));
        }

        /** @var callable $callable */
        $callable = [$this->aggregateRootName, 'reconstitute'];

        $aggregateRoot = \call_user_func($callable, $aggregateId, $eventStream);

        if ($aggregateRoot instanceof AggregateRootDeletableInterface && $aggregateRoot->isDeleted()) {
            throw new AggregateRootDeletedException(\sprintf(
                'AggregateRoot with id %s deleted',
                $aggregateId->value()
            ));
        }

        return $aggregateRoot;
    }

    public function save(AggregateRootInterface $aggregateRoot): void
    {
        $events = $aggregateRoot->releaseRecordedEvents();

        foreach ($events->events() as $event) {
            $event = $this->eventDecorator->decorate($event);

            $this->eventStore->append($event);

            $this->eventBus->handle($event);
        }
    }
}
