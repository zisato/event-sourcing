<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;

class PrivateDataEventPayloadService implements PrivateDataEventServiceInterface
{
    private PayloadKeyCollectionStrategyInterface $payloadKeyCollectionStrategy;

    private PrivateDataPayloadServiceInterface $privateDataPayloadService;

    public function __construct(
        PayloadKeyCollectionStrategyInterface $payloadKeyCollectionStrategy,
        PrivateDataPayloadServiceInterface $privateDataPayloadService
    ) {
        $this->payloadKeyCollectionStrategy = $payloadKeyCollectionStrategy;
        $this->privateDataPayloadService = $privateDataPayloadService;
    }

    public function hidePrivateData(EventInterface $event): EventInterface
    {
        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        if ($payloadKeys->isEmpty()) {
            return $event;
        }

        $payload = Payload::create($event->aggregateId(), $event->payload(), $payloadKeys);

        $newPayload = $this->privateDataPayloadService->hide($payload);

        return $this->createNewEvent($event, $newPayload);
    }

    public function showPrivateData(EventInterface $event): EventInterface
    {
        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        if ($payloadKeys->isEmpty()) {
            return $event;
        }

        $payload = Payload::create($event->aggregateId(), $event->payload(), $payloadKeys);

        $forgottenValues = false;

        try {
            $newPayload = $this->privateDataPayloadService->show($payload);
        } catch (ForgottedPrivateDataException $exception) {
            $newPayload = $payload->changeValues(function ($value) {
                return null;
            });

            $forgottenValues = true;
        }

        $newEvent = $this->createNewEvent($event, $newPayload);

        if ($forgottenValues) {
            $newEvent = $newEvent->withMetadata(self::METADATA_KEY_EVENT_FORGOTTEN_VALUES, true);
        }

        return $newEvent;
    }

    /**
     * @param array<string, mixed> $newPayload
     */
    private function createNewEvent(EventInterface $event, array $newPayload): EventInterface
    {
        /** @var callable $callable */
        $callable = [\get_class($event), 'reconstitute'];

        return \call_user_func(
            $callable,
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $newPayload,
            $event->version(),
            $event->metadata()
        );
    }
}
