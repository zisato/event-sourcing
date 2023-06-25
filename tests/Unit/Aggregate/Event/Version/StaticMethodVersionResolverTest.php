<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Version;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventWithVersionStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver
 */
class StaticMethodVersionResolverTest extends TestCase
{
    /**
     * @dataProvider getResolveData
     */
    public function testResolve(EventInterface $event, string $method, int $expectedVersion): void
    {
        $resolver = new StaticMethodVersionResolver($method);

        $version = $resolver->resolve($event);

        $this->assertEquals($expectedVersion, $version);
    }

    /**
     * @dataProvider getResolveMethodNotExistsData
     */
    public function testResolveMethodNotExists(EventInterface $event): void
    {
        $resolver = new StaticMethodVersionResolver();
        
        $version = $resolver->resolve($event);

        $this->assertEquals($event->version(), $version);
    }

    public static function getResolveData(): array
    {
        return [
            [
                EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002'),
                'defaultVersion',
                1
            ],
            [
                EventWithVersionStub::occur('34e13904-f3fc-11ec-b939-0242ac120002'),
                'latestVersion',
                3
            ],
        ];
    }

    public static function getResolveMethodNotExistsData(): array
    {
        return [
            [
                EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002'),
                'defaultVersion',
            ],
            [
                EventStub::occur('34e13904-f3fc-11ec-b939-0242ac120002'),
                'latestVersion',
            ],
        ];
    }
}
