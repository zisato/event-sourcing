<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface UpcasterInterface
{
    public function canUpcast(EventInterface $event): bool;

    public function upcast(EventInterface $event): EventInterface;
}
