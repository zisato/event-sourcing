<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\ValueObject;

final class Version
{
    /**
     * @var int
     */
    private const VALUE_MIN = 0;

    private readonly int $value;

    final private function __construct(int $value)
    {
        $this->checkValidValue($value);

        $this->value = $value;
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(self::VALUE_MIN);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function next(): self
    {
        return new self($this->value + 1);
    }

    public function equals(self $version): bool
    {
        return $this->value === $version->value();
    }

    private function checkValidValue(int $value): void
    {
        if ($value < self::VALUE_MIN) {
            throw new \InvalidArgumentException(\sprintf(
                'Invalid Version value. Min allowed: %d',
                self::VALUE_MIN
            ));
        }
    }
}
