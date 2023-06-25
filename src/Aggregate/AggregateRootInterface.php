<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate;

use Zisato\EventSourcing\Aggregate\Event\Stream\EventStreamInterface;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Identity\IdentityInterface;

interface AggregateRootInterface
{
    public static function reconstitute(IdentityInterface $id, EventStreamInterface $eventStream): self;

    public function id(): IdentityInterface;

    public function version(): Version;

    public function replyEvents(EventStreamInterface $eventStream): void;

    public function releaseRecordedEvents(): EventStreamInterface;

    public function hasRecordedEvents(): bool;
}
