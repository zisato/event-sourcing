<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Identity;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;
use Zisato\EventSourcing\Identity\GenerableIdentityInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

final class UUID implements GenerableIdentityInterface
{
    private readonly string $value;

    final private function __construct(UuidInterface $value)
    {
        $this->value = $value->toString();
    }

    public static function fromString(string $value): IdentityInterface
    {
        $uuid = RamseyUuid::fromString($value);

        return new self($uuid);
    }

    public static function generate(): IdentityInterface
    {
        $uuid = RamseyUuid::uuid1();

        return new self($uuid);
    }

    public function value(): string
    {
        return $this->value;
    }
}
