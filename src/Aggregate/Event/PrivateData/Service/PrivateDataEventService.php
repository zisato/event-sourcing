<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;

class PrivateDataEventService implements PrivateDataEventServiceInterface
{
    private PayloadKeyCollectionStrategyInterface $payloadKeyCollectionStrategy;

    private PayloadEncoderAdapterInterface $payloadEncoderAdapter;

    public function __construct(
        PayloadKeyCollectionStrategyInterface $payloadKeyCollectionStrategy,
        PayloadEncoderAdapterInterface $payloadEncoderAdapter
    ) {
        $this->payloadKeyCollectionStrategy = $payloadKeyCollectionStrategy;
        $this->payloadEncoderAdapter = $payloadEncoderAdapter;
    }

    public function hidePrivateData(EventInterface $event): EventInterface
    {
        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        if ($payloadKeys->isEmpty()) {
            return $event;
        }

        $payload = Payload::create($event->aggregateId(), $event->payload(), $payloadKeys, $this->payloadEncoderAdapter);

        $payload->hide();

        return $this->createNewEvent($event, $payload->payload());
    }

    public function showPrivateData(EventInterface $event): EventInterface
    {
        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        if ($payloadKeys->isEmpty()) {
            return $event;
        }

        $payload = Payload::create($event->aggregateId(), $event->payload(), $payloadKeys, $this->payloadEncoderAdapter);

        $forgottenPrivateData = false;

        try {
            $payload->show();
        } catch (ForgottedPrivateDataException $exception) {
            $payload->forget();

            $forgottenPrivateData = true;
        }

        $newEvent = $this->createNewEvent($event, $payload->payload());

        if ($forgottenPrivateData) {
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
