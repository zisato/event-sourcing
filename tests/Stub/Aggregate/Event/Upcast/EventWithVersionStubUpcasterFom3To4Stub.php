<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventWithVersionStub;

class EventWithVersionStubUpcasterFom3To4Stub implements UpcasterInterface
{
    private const VERSION_FROM = 3;
    private const VERSION_TO = 4;
    
    public function canUpcast(EventInterface $event): bool
    {
        return $event instanceof EventWithVersionStub && $event->version() === self::VERSION_FROM;
    }

    public function upcast(EventInterface $event): EventWithVersionStub
    {
        $payload = $event->payload();
        
        $payload['upcaster_stub_3_to_4'] = true;

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
