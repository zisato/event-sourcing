<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use DateTimeImmutable;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

final class Snapshot implements SnapshotInterface
{
    final private function __construct(
        private readonly AggregateRootInterface $aggregateRoot,
        private readonly DateTimeImmutable $createdAt
    ) {
    }

    public static function create(
        AggregateRootInterface $aggregateRoot,
        DateTimeImmutable $createdAt
    ): SnapshotInterface {
        return new self($aggregateRoot, $createdAt);
    }

    public function aggregateRoot(): AggregateRootInterface
    {
        return $this->aggregateRoot;
    }

    public function aggregateRootClassName(): string
    {
        return $this->aggregateRoot::class;
    }

    public function aggregateRootId(): IdentityInterface
    {
        return $this->aggregateRoot->id();
    }

    public function aggregateRootVersion(): Version
    {
        return $this->aggregateRoot->version();
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
