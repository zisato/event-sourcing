<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;

interface PayloadEncoderAdapterInterface
{
    public function show(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array;

    public function hide(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array;

    public function forget(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array;
}
