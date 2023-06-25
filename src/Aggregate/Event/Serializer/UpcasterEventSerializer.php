<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Serializer;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;

final class UpcasterEventSerializer implements EventSerializerInterface
{
    public function __construct(
        private readonly EventSerializerInterface $eventSerializer,
        private readonly UpcasterInterface $upcaster
    ) {
    }

    public function fromArray(array $data): EventInterface
    {
        $event = $this->eventSerializer->fromArray($data);

        if ($this->upcaster->canUpcast($event)) {
            return $this->upcaster->upcast($event);
        }

        return $event;
    }

    public function toArray(EventInterface $event): array
    {
        return $this->eventSerializer->toArray($event);
    }
}
