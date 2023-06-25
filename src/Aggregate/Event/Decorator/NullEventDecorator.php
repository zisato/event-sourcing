<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Decorator;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

final class NullEventDecorator implements EventDecoratorInterface
{
    public function decorate(EventInterface $event): EventInterface
    {
        return $event;
    }
}
