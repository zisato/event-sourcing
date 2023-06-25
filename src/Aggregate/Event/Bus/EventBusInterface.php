<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Bus;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface EventBusInterface
{
    public function handle(EventInterface $event): void;
}
