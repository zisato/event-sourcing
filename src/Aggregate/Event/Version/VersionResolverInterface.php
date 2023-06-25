<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Version;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface VersionResolverInterface
{
    public function resolve(EventInterface $event): int;
}
