<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

class EventStubUpcasterFom1To2Stub implements UpcasterInterface
{
    private const VERSION_FROM = 1;
    private const VERSION_TO = 2;
    
    public function canUpcast(EventInterface $event): bool
    {
        return $event instanceof EventStub && $event->version() === self::VERSION_FROM;
    }

    public function upcast(EventInterface $event): EventStub
    {
        $payload = $event->payload();
        
        $payload['upcaster_stub_1_to_2'] = true;

        return $event::reconstitute(
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $payload,
            self::VERSION_TO,
            $event->metadata()
        );
    }
}
