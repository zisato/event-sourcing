<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event;

use DateTimeImmutable;
use Zisato\EventSourcing\Event\Event;

abstract class AbstractEvent extends Event implements EventInterface
{
    /**
     * @var int
     */
    private const DEFAULT_AGGREGATE_VERSION = 0;

    /**
     * @var int
     */
    private const DEFAULT_VERSION = 1;

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    final protected function __construct(
        private readonly string $aggregateId,
        private readonly int $aggregateVersion,
        DateTimeImmutable $createdAt,
        array $payload,
        private readonly int $version,
        private readonly array $metadata
    ) {
        parent::__construct($createdAt, $payload);
    }

    public static function occur(string $aggregateId, array $payload = []): EventInterface
    {
        return new static(
            $aggregateId,
            self::DEFAULT_AGGREGATE_VERSION,
            new DateTimeImmutable(),
            $payload,
            self::DEFAULT_VERSION,
            []
        );
    }

    public static function reconstitute(
        string $aggregateId,
        int $aggregateVersion,
        DateTimeImmutable $createdAt,
        array $payload,
        int $version,
        array $metadata
    ): EventInterface {
        return new static($aggregateId, $aggregateVersion, $createdAt, $payload, $version, $metadata);
    }

    public function withMetadata(string $key, $value): EventInterface
    {
        $newMetadata = $this->metadata;

        $newMetadata[$key] = $value;

        return new static(
            $this->aggregateId,
            $this->aggregateVersion,
            $this->createdAt(),
            $this->payload(),
            $this->version,
            $newMetadata
        );
    }

    public function withAggregateVersion(int $aggregateVersion): EventInterface
    {
        return new static(
            $this->aggregateId,
            $aggregateVersion,
            $this->createdAt(),
            $this->payload(),
            $this->version,
            $this->metadata,
        );
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}
