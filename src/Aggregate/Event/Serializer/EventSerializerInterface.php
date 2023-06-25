<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Serializer;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface EventSerializerInterface
{
    /**
     * @var string
     */
    public const DATE_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): EventInterface;

    /**
     * @return array<string, mixed>
     */
    public function toArray(EventInterface $event): array;
}
