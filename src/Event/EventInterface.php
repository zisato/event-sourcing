<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Event;

interface EventInterface
{
    public function createdAt(): \DateTimeImmutable;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;
}
