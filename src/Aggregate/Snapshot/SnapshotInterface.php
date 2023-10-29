<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Snapshot;

use DateTimeImmutable;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

interface SnapshotInterface
{
    public static function create(AggregateRootInterface $aggregateRoot, DateTimeImmutable $createdAt): self;

    public function aggregateRoot(): AggregateRootInterface;

    public function aggregateRootClassName(): string;

    public function aggregateRootId(): IdentityInterface;

    public function aggregateRootVersion(): Version;

    public function createdAt(): DateTimeImmutable;
}
