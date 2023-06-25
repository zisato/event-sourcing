<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

class Snapshot implements SnapshotInterface
{
    private AggregateRootInterface $aggregateRoot;

    private \DateTimeImmutable $createdAt;

    final protected function __construct(AggregateRootInterface $aggregateRoot, \DateTimeImmutable $createdAt)
    {
        $this->aggregateRoot = $aggregateRoot;
        $this->createdAt = $createdAt;
    }

    public static function create(
        AggregateRootInterface $aggregateRoot,
        \DateTimeImmutable $createdAt
    ): SnapshotInterface {
        return new static($aggregateRoot, $createdAt);
    }

    public function aggregateRoot(): AggregateRootInterface
    {
        return $this->aggregateRoot;
    }

    public function aggregateRootClassName(): string
    {
        return \get_class($this->aggregateRoot);
    }

    public function aggregateRootId(): IdentityInterface
    {
        return $this->aggregateRoot->id();
    }

    public function aggregateRootVersion(): Version
    {
        return $this->aggregateRoot->version();
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
