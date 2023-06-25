<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Version;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;

final class StaticMethodVersionResolver implements VersionResolverInterface
{
    /**
     * @var string
     */
    private const METHOD_NAME = 'defaultVersion';

    public function __construct(private readonly string $method = self::METHOD_NAME)
    {
    }

    public function resolve(EventInterface $event): int
    {
        if (\method_exists($event, $this->method)) {
            /** @var callable $callable */
            $callable = [$event, $this->method];

            return \call_user_func($callable);
        }

        return $event->version();
    }
}
