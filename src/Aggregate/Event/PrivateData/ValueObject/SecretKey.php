<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

use InvalidArgumentException;

final class SecretKey
{
    private readonly string $value;

    private function __construct(string $value)
    {
        $this->assertInvalidValue($value);

        $this->value = $value;
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private function assertInvalidValue(string $value): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException();
        }
    }
}
