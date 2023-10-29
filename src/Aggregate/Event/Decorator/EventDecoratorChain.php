<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Decorator;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

final class EventDecoratorChain implements EventDecoratorInterface
{
    /**
     * @var EventDecoratorInterface[]
     */
    private readonly array $decorators;

    public function __construct(EventDecoratorInterface ...$decorators)
    {
        $this->decorators = $decorators;
    }

    public function decorate(EventInterface $event): EventInterface
    {
        foreach ($this->decorators as $decorator) {
            $event = $decorator->decorate($event);
        }

        return $event;
    }
}
