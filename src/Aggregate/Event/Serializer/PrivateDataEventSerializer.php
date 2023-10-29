<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Serializer;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;

final class PrivateDataEventSerializer implements EventSerializerInterface
{
    public function __construct(private readonly EventSerializerInterface $eventSerializer, private readonly PrivateDataEventServiceInterface $privateDataEventService)
    {
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
