<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer;

use Zisato\EventSourcing\JSON\JSON;

final class JsonPayloadValueSerializer implements PayloadValueSerializerInterface
{
    public function toString(mixed $value): string
    {
        return JSON::encode($value);
    }

    public function fromString(string $value): mixed
    {
        return JSON::decode($value);
    }
}
