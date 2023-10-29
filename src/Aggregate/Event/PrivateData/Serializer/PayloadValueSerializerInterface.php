<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer;

interface PayloadValueSerializerInterface
{
    public function toString(mixed $value): string;

    public function fromString(string $value): mixed;
}
