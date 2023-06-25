<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStream;
use Zisato\EventSourcing\Aggregate\Event\Stream\EventStreamInterface;
use Zisato\EventSourcing\Aggregate\Exception\AggregateReconstituteException;
use Zisato\EventSourcing\Aggregate\Exception\InvalidAggregateVersionException;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

abstract class AbstractAggregateRoot implements AggregateRootInterface
{
    private Version $version;

    private EventStreamInterface $recordedEvents;

    final protected function __construct(private readonly IdentityInterface $id)
    {
        $this->version = Version::zero();
        $this->recordedEvents = EventStream::create();
    }

    public static function reconstitute(
        IdentityInterface $id,
        EventStreamInterface $eventStream
    ): AggregateRootInterface {
        if ($eventStream->isEmpty()) {
            throw new AggregateReconstituteException('Cannot reconstitute aggregate from empty event stream');
        }

        $instance = new static($id);

        $instance->replyEvents($eventStream);

        return $instance;
    }

    public function id(): IdentityInterface
    {
        return $this->id;
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function replyEvents(EventStreamInterface $eventStream): void
    {
        foreach ($eventStream->events() as $event) {
            $this->apply($event);
        }
    }

    public function releaseRecordedEvents(): EventStreamInterface
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = EventStream::create();

        return $recordedEvents;
    }

    public function hasRecordedEvents(): bool
    {
        return ! $this->recordedEvents->isEmpty();
    }

    protected function recordThat(EventInterface $event): void
    {
        $event = $event->withAggregateVersion($this->version->next()->value());

        $this->apply($event);

        $this->recordedEvents->add($event);
    }

    private function apply(EventInterface $event): void
    {
        $nextVersion = Version::create($event->aggregateVersion());

        if (! $nextVersion->equals($this->version->next())) {
            throw new InvalidAggregateVersionException(\sprintf(
                'Cannot apply event %s with aggregate version %d, it must follow current aggregate version %d',
                $event::class,
                $event->aggregateVersion(),
                $this->version->value()
            ));
        }

        $this->callApplyMethod($event);

        $this->version = $nextVersion;
    }

    private function callApplyMethod(EventInterface $event): void
    {
        $className = \explode('\\', $event::class);
        $shortName = \array_pop($className);
        $method = 'apply' . $shortName;

        if (\method_exists($this, $method)) {
            $this->{$method}($event);
        }
    }
}
