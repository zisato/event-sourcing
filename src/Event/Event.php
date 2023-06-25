<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Event;

class Event implements EventInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly \DateTimeImmutable $createdAt,
        private readonly array $payload
    ) {
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}
