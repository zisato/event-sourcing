<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\DeletedKeyException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\KeyNotFoundException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;

final class CryptoPayloadEncoderAdapter implements PayloadEncoderAdapterInterface
{
    public function __construct(
        private readonly PayloadValueSerializerInterface $payloadValueSerializer,
        private readonly SecretKeyStoreInterface $secretKeyStore,
        private readonly CryptoInterface $crypto
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function hide(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $secretKey = $this->getSecretKey($aggregateId);

        return $this->encrypt($secretKey, $payloadKeyCollection, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function show(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        try {
            $secretKey = $this->secretKeyStore->get($aggregateId);
        } catch (DeletedKeyException $exception) {
            throw new ForgottedPrivateDataException($exception->getMessage());
        } catch (KeyNotFoundException) {
            return $payload;
        }

        return $this->decrypt($secretKey, $payloadKeyCollection, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function forget(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $ref = null;
        }

        return $newPayload;
    }

    private function getSecretKey(string $aggregateId): SecretKey
    {
        try {
            $secretKey = $this->secretKeyStore->get($aggregateId);
        } catch (KeyNotFoundException) {
            $secretKey = $this->crypto->generateSecretKey();

            $this->secretKeyStore->save($aggregateId, $secretKey);
        }

        return $secretKey;
    }

    private function encrypt(SecretKey $secretKey, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $normalizedValue = $this->payloadValueSerializer->toString($ref);
            $ref = $this->crypto->encrypt($normalizedValue, $secretKey);
        }

        return $newPayload;
    }

    private function decrypt(SecretKey $secretKey, PayloadKeyCollection $payloadKeyCollection, array $payload): array
    {
        $newPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $decryptedValue = $this->crypto->decrypt((string) $ref, $secretKey);

            $ref = $this->payloadValueSerializer->fromString($decryptedValue);
        }

        return $newPayload;
    }
}
