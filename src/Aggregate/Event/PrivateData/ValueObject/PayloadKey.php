<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

class PayloadKey
{
    /**
     * @var array<string>
     */
    private array $values;

    public function __construct(string ...$values)
    {
        $this->values = $values;
    }

    public static function create(string ...$values): self
    {
        return new self(...$values);
    }

    /**
     * @return iterable<string>
     */
    public function values(): iterable
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }
}
