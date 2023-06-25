<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Decorator;

use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;

class EventStubBarDecorator implements EventDecoratorInterface
{
    public function decorate(EventInterface $event): EventInterface
    {
        return $event->withMetadata('bar', 'bar');
    }
}
