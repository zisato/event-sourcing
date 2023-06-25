<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Decorator;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface EventDecoratorInterface
{
    public function decorate(EventInterface $event): EventInterface;
}
