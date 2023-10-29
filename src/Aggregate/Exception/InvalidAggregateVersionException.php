<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Exception;

use RuntimeException;

final class InvalidAggregateVersionException extends RuntimeException
{
}
