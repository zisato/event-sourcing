<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;

interface PrivateDataPayloadInterface
{
    public function privateDataPayloadKeys(): PayloadKeyCollection;
}
