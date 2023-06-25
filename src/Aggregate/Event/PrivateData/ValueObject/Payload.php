<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

class Payload
{
    private string $aggregateId;

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    private PayloadKeyCollection $payloadKeyCollection;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(string $aggregateId, array $payload, PayloadKeyCollection $payloadKeyCollection)
    {
        $this->assertKeyNotExists($payload, $payloadKeyCollection);

        $this->aggregateId = $aggregateId;
        $this->payload = $payload;
        $this->payloadKeyCollection = $payloadKeyCollection;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(
        string $aggregateId,
        array $payload,
        PayloadKeyCollection $payloadKeyCollection
    ): self {
        return new self($aggregateId, $payload, $payloadKeyCollection);
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

    /**
     * @return array<string, mixed>
     */
    public function changeValues(callable $setter): array
    {
        foreach ($this->payloadKeyCollection->values() as $payloadKey) {
            $ref = &$this->payload;

            foreach ($payloadKey->values() as $key) {
                $ref = &$ref[$key];
            }

            $ref = $setter($ref);
        }

        return $this->payload;
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
                    throw new \InvalidArgumentException(\sprintf('Key %s not extist in payload', $key));
                }

                $ref = &$ref[$key];
            }
        }
    }
}
