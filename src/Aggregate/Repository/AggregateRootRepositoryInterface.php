<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Repository;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

interface AggregateRootRepositoryInterface
{
    public function get(IdentityInterface $aggregateId): AggregateRootInterface;

    public function save(AggregateRootInterface $aggregateRoot): void;
}
