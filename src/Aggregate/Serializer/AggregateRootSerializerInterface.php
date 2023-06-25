<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Serializer;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

interface AggregateRootSerializerInterface
{
    public function serialize(AggregateRootInterface $aggregateRoot): string;

    public function deserialize(string $aggregateRoot): AggregateRootInterface;
}
