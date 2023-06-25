<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event;

use DateTimeImmutable;
use Zisato\EventSourcing\Event\EventInterface as BaseEventInterface;

interface EventInterface extends BaseEventInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function occur(string $aggregateId, array $payload): self;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public static function reconstitute(
        string $aggregateId,
        int $aggregateVersion,
        DateTimeImmutable $createdAt,
        array $payload,
        int $version,
        array $metadata
    ): self;

    /**
     * @param mixed $value
     */
    public function withMetadata(string $key, $value): self;

    public function withAggregateVersion(int $aggregateVersion): self;

    public function aggregateId(): string;

    public function aggregateVersion(): int;

    public function version(): int;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;
}
