<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate;

use Zisato\EventSourcing\Aggregate\AggregateRootDeletableInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventDeleteStub;

class AggregateRootDeletableStub extends AggregateRootStub implements AggregateRootDeletableInterface
{
    private bool $deleted = false;

    public function delete(): void
    {
        $this->recordThat(EventDeleteStub::occur($this->id()->value()));
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    protected function applyEventDeleteStub(EventDeleteStub $event): void
    {
        $this->deleted = true;
    }
}
