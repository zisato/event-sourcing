<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Identity;

interface IdentityInterface
{
    public static function fromString(string $value): self;

    public function value(): string;
}
