<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Stream;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

final class EventStream implements EventStreamInterface
{
    /**
     * @var EventInterface[]
     */
    private array $events = [];

    final private function __construct()
    {
    }

    public static function create(): EventStreamInterface
    {
        return new self();
    }

    public function add(EventInterface $event): void
    {
        $this->events[] = $event;
    }

    public function count(): int
    {
        return count($this->events);
    }

    /**
     * @return \Iterator<EventInterface>
     */
    public function events(): \Iterator
    {
        yield from $this->events;
    }

    public function isEmpty(): bool
    {
        return $this->events === [];
    }
}
