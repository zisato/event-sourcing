<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\PrivateDataPayloadInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;

final class PayloadKeyCollectionByEventInterface implements PayloadKeyCollectionStrategyInterface
{
    public function payloadKeys(EventInterface $event): PayloadKeyCollection
    {
        if ($event instanceof PrivateDataPayloadInterface) {
            return $event->privateDataPayloadKeys();
        }

        return PayloadKeyCollection::create();
    }
}
