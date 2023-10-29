<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository;

use Zisato\EventSourcing\Identity\IdentityInterface;

interface PrivateDataRepositoryInterface
{
    public function get(string $aggregateId, IdentityInterface $valueId): mixed;

    public function save(string $aggregateId, IdentityInterface $valueId, mixed $value): void;
}
