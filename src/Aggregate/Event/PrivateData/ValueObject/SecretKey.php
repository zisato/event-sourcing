<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject;

class SecretKey
{
    private string $value;

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
        if (empty(trim($value))) {
            throw new \InvalidArgumentException();
        }
    }
}
