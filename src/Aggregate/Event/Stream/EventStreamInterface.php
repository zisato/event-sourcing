<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Stream;

use Countable;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface EventStreamInterface extends Countable
{
    public function add(EventInterface $event): void;

    /**
     * @return iterable<EventInterface>
     */
    public function events(): iterable;

    public function isEmpty(): bool;
}
