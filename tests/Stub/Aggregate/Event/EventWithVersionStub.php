<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate\Event;

use Zisato\EventSourcing\Aggregate\Event\AbstractEvent;

class EventWithVersionStub extends AbstractEvent
{
    public const DEFAULT_VERSION = 3;

    public static function latestVersion(): int
    {
        return static::DEFAULT_VERSION;
    }
}
