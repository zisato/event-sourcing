<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Identity\UUID;

final class ExternalPayloadEncoderAdapter implements PayloadEncoderAdapterInterface
{
    public function __construct(private readonly PrivateDataRepositoryInterface $privateDataRepository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function hide(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        return $this->saveValues($aggregateId, $payloadKeyCollection, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function show(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        return $this->getValues($aggregateId, $payloadKeyCollection, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function forget(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $ref = null;
        }

        return $newPayload;
    }

    private function saveValues(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $valueId = UUID::generate();

            $this->privateDataRepository->save($aggregateId, $valueId, $ref);

            $ref = $valueId->value();
        }

        return $newPayload;
    }

    private function getValues(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $valueId = UUID::fromString((string) $ref);

            $ref = $this->privateDataRepository->get($aggregateId, $valueId);
        }

        return $newPayload;
    }
}
