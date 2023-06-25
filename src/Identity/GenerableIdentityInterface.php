<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Identity;

interface GenerableIdentityInterface extends IdentityInterface
{
    public static function generate(): IdentityInterface;
}
