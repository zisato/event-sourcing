<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

class PayloadKeyCollection
{
    /**
     * @var array<PayloadKey>
     */
    private array $values = [];

    private function __construct(PayloadKey ...$values)
    {
        $this->values = $values;
    }

    public static function create(PayloadKey ...$values): self
    {
        return new self(...$values);
    }

    /**
     * @return iterable<PayloadKey>
     */
    public function values(): iterable
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    public function isEmpty(): bool
    {
        return $this->values === [];
    }
}
