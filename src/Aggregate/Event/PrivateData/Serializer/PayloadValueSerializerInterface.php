<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer;

interface PayloadValueSerializerInterface
{
    /**
     * @param mixed $value
     */
    public function toString($value): string;

    /**
     * @return mixed
     */
    public function fromString(string $value);
}
