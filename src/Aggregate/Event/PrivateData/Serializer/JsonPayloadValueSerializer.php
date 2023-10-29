<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer;

use Zisato\EventSourcing\JSON\JSON;

final class JsonPayloadValueSerializer implements PayloadValueSerializerInterface
{
    /**
     * @param mixed $value
     */
    public function toString($value): string
    {
        return JSON::encode($value);
    }

    /**
     * @return mixed
     */
    public function fromString(string $value)
    {
        return JSON::decode($value);
    }
}
