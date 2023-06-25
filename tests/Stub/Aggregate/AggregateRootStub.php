<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate;

use Zisato\EventSourcing\Aggregate\AbstractAggregateRoot;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

class AggregateRootStub extends AbstractAggregateRoot
{
    public static function fromIdentity(IdentityInterface $identity): AggregateRootStub
    {
        return new self($identity);
    }

    public static function fromEvent(IdentityInterface $identity, EventInterface $event): AggregateRootStub
    {
        $instance = new self($identity);

        $instance->recordThat($event);

        return $instance;
    }

    protected function applyEventStub(EventStub $event): void
    {

    }
}
