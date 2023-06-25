<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Serializer;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;

class PrivateDataEventSerializer implements EventSerializerInterface
{
    private EventSerializerInterface $eventSerializer;

    private PrivateDataEventServiceInterface $privateDataEventService;

    public function __construct(
        EventSerializerInterface $eventSerializer,
        PrivateDataEventServiceInterface $privateDataEventService
    ) {
        $this->eventSerializer = $eventSerializer;
        $this->privateDataEventService = $privateDataEventService;
    }

    public function fromArray(array $data): EventInterface
    {
        $event = $this->eventSerializer->fromArray($data);

        return $this->privateDataEventService->showPrivateData($event);
    }

    public function toArray(EventInterface $event): array
    {
        $event = $this->privateDataEventService->hidePrivateData($event);

        return $this->eventSerializer->toArray($event);
    }
}
