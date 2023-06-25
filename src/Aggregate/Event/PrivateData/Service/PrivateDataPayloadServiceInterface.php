<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;

interface PrivateDataPayloadServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function hide(Payload $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function show(Payload $payload): array;
}
