<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

use InvalidArgumentException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;

final class Payload
{
    /**
     * @var array<string, mixed>
     */
    private array $payload = [];

    private readonly PayloadKeyCollection $payloadKeyCollection;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        private readonly string $aggregateId,
        array $payload,
        PayloadKeyCollection $payloadKeyCollection,
        private readonly PayloadEncoderAdapterInterface $payloadEncoderAdapter
    ) {
        $this->assertKeyNotExists($payload, $payloadKeyCollection);
        $this->payload = $payload;
        $this->payloadKeyCollection = $payloadKeyCollection;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(
        string $aggregateId,
        array $payload,
        PayloadKeyCollection $payloadKeyCollection,
        PayloadEncoderAdapterInterface $payloadEncoderAdapter
    ): self {
        return new self($aggregateId, $payload, $payloadKeyCollection, $payloadEncoderAdapter);
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function payloadKeyCollection(): PayloadKeyCollection
    {
        return $this->payloadKeyCollection;
    }

    public function show(): void
    {
        $this->payload = $this->payloadEncoderAdapter->show(
            $this->aggregateId,
            $this->payloadKeyCollection,
            $this->payload
        );
    }

    public function hide(): void
    {
        $this->payload = $this->payloadEncoderAdapter->hide(
            $this->aggregateId,
            $this->payloadKeyCollection,
            $this->payload
        );
    }

    public function forget(): void
    {
        $newPayload = $this->payload();

        foreach ($this->payloadKeyCollection->values() as $payloadKey) {
            $ref = &$newPayload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $ref = null;
        }

        $this->payload = $newPayload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertKeyNotExists(array $payload, PayloadKeyCollection $payloadKeyCollection): void
    {
        $refPayload = $payload;

        foreach ($payloadKeyCollection->values() as $payloadKey) {
            $ref = &$refPayload;

            foreach ($payloadKey->values() as $key) {
                if (! array_key_exists($key, $ref)) {
                    throw new InvalidArgumentException(\sprintf('Key %s not extist in payload', $key));
                }

                $ref = &$ref[$key];
            }
        }
    }
}
