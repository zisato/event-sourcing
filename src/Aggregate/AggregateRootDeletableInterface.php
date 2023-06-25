<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate;

interface AggregateRootDeletableInterface
{
    public function delete(): void;

    public function isDeleted(): bool;
}
