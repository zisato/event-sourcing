<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;

interface CryptoInterface
{
    public function generateSecretKey(): SecretKey;

    public function encrypt(string $message, SecretKey $secretKey): string;

    public function decrypt(string $message, SecretKey $secretKey): string;
}
