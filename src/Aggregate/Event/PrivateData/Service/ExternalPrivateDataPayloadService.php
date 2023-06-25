<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Identity\UUID;

class ExternalPrivateDataPayloadService implements PrivateDataPayloadServiceInterface
{
    private PrivateDataRepositoryInterface $privateDataRepository;

    public function __construct(PrivateDataRepositoryInterface $privateDataRepository)
    {
        $this->privateDataRepository = $privateDataRepository;
    }

    public function hide(Payload $payload): array
    {
        $newPayload = $payload->changeValues(function ($value) use ($payload) {
            $valueId = UUID::generate();

            $this->privateDataRepository->save($payload->aggregateId(), $valueId, $value);

            return $valueId->value();
        });

        return $newPayload;
    }

    public function show(Payload $payload): array
    {
        $newPayload = $payload->changeValues(function ($value) use ($payload) {
            $valueId = UUID::fromString($value);

            return $this->privateDataRepository->get($payload->aggregateId(), $valueId);
        });

        return $newPayload;
    }
}
