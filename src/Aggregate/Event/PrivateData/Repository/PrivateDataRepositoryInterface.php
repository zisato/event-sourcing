<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository;

use Zisato\EventSourcing\Identity\IdentityInterface;

interface PrivateDataRepositoryInterface
{
    /**
     * @return mixed
     */
    public function get(string $aggregateId, IdentityInterface $valueId);

    /**
     * @param mixed $value
     */
    public function save(string $aggregateId, IdentityInterface $valueId, $value): void;
}
