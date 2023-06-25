<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;

interface PayloadKeyCollectionStrategyInterface
{
    public function payloadKeys(EventInterface $event): PayloadKeyCollection;
}
