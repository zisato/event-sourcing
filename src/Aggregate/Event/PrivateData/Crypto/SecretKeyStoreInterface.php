<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;

interface SecretKeyStoreInterface
{
    public function save(string $aggregateId, SecretKey $key): void;

    public function get(string $aggregateId): SecretKey;

    public function forget(string $aggregateId): void;
}
