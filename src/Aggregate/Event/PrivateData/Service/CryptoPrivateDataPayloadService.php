<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Service;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\DeletedKeyException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\KeyNotFoundException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;

class CryptoPrivateDataPayloadService implements PrivateDataPayloadServiceInterface
{
    private PayloadValueSerializerInterface $payloadValueSerializer;

    private SecretKeyStoreInterface $secretKeyStore;

    private CryptoInterface $crypto;

    public function __construct(
        PayloadValueSerializerInterface $payloadValueSerializer,
        SecretKeyStoreInterface $secretKeyStore,
        CryptoInterface $crypto
    ) {
        $this->payloadValueSerializer = $payloadValueSerializer;
        $this->secretKeyStore = $secretKeyStore;
        $this->crypto = $crypto;
    }

    public function hide(Payload $payload): array
    {
        try {
            $secretKey = $this->secretKeyStore->get($payload->aggregateId());
        } catch (KeyNotFoundException $exception) {
            $secretKey = $this->crypto->generateSecretKey();

            $this->secretKeyStore->save($payload->aggregateId(), $secretKey);
        }

        $newPayload = $payload->changeValues(function ($value) use ($secretKey) {
            $value = $this->payloadValueSerializer->toString($value);

            return $this->crypto->encrypt($value, $secretKey);
        });

        return $newPayload;
    }

    public function show(Payload $payload): array
    {
        try {
            $secretKey = $this->secretKeyStore->get($payload->aggregateId());

            $newPayload = $payload->changeValues(function ($value) use ($secretKey) {
                $value = $this->crypto->decrypt($value, $secretKey);

                return $this->payloadValueSerializer->fromString($value);
            });
        } catch (DeletedKeyException $exception) {
            throw new ForgottedPrivateDataException($exception->getMessage());
        } catch (KeyNotFoundException $exception) {
            $newPayload = $payload->payload();
        }

        return $newPayload;
    }
}
