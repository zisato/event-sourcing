<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

final class EventUpcasterChain implements UpcasterInterface
{
    /**
     * @var UpcasterInterface[]
     */
    private readonly array $upcasters;

    public function __construct(UpcasterInterface ...$upcasters)
    {
        $this->upcasters = $upcasters;
    }

    public function canUpcast(EventInterface $event): bool
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->canUpcast($event)) {
                return true;
            }
        }

        return false;
    }

    public function upcast(EventInterface $event): EventInterface
    {
        foreach ($this->upcasters as $upcaster) {
            if ($upcaster->canUpcast($event)) {
                $event = $upcaster->upcast($event);
            }
        }

        return $event;
    }
}
