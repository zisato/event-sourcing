<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\GenerateSecretKeyException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\EncryptException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\DecryptException;

class OpenSSLCrypto implements CryptoInterface
{
    private const IV_LENGTH = 16;

    private const KEY_LENGTH = 64;

    private const METHOD = 'AES256';

    private const OPTIONS = 0;

    public function generateSecretKey(): SecretKey
    {
        $secretKey = \openssl_random_pseudo_bytes(self::KEY_LENGTH);

        if ($secretKey === false) {
            throw new GenerateSecretKeyException('Could not create secret key.');
        }

        return SecretKey::create(\base64_encode($secretKey));
    }

    public function encrypt(string $message, SecretKey $key): string
    {
        $iv = $this->generateIV();

        $encrypted = \openssl_encrypt($message, self::METHOD, $key->value(), self::OPTIONS, $iv);

        if ($encrypted === false) {
            throw new EncryptException('Could not encrypt message.');
        }

        return \base64_encode($iv . $encrypted);
    }

    public function decrypt(string $message, SecretKey $key): string
    {
        $decoded = \base64_decode($message, true);

        $iv = \substr($decoded, 0, self::IV_LENGTH);
        $data = \substr($decoded, self::IV_LENGTH);

        $decrypted = \openssl_decrypt($data, self::METHOD, $key->value(), self::OPTIONS, $iv);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt message.');
        }

        return $decrypted;
    }

    private function generateIV(): string
    {
        $iv = \openssl_random_pseudo_bytes(self::IV_LENGTH);

        if ($iv === false) {
            throw new EncryptException('Could not generate IV.');
        }

        return $iv;
    }
}
