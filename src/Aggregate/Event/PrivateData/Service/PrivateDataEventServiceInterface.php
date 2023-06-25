<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

interface PrivateDataEventServiceInterface
{
    public const METADATA_KEY_EVENT_FORGOTTEN_VALUES = 'event_forgotten_values';

    public function hidePrivateData(EventInterface $event): EventInterface;

    public function showPrivateData(EventInterface $event): EventInterface;
}
