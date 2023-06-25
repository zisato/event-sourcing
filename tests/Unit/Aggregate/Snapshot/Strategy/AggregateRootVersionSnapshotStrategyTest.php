<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Snapshot\Strategy;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy
 */
class AggregateRootVersionSnapshotStrategyTest extends TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testShouldCreateSnapshotMethod(
        int $versionToCreateSnapshot,
        int $totalEvents,
        bool $expected
    ): void {
        $snapshotStrategy = new AggregateRootVersionSnapshotStrategy($versionToCreateSnapshot);
        /** @var AggregateRootInterface|MockObject $aggregateRoot */
        $aggregateRoot = $this->createMock(AggregateRootInterface::class);
        $version = Version::create($totalEvents);

        $aggregateRoot->expects($this->once())
            ->method('version')
            ->willReturn($version);

        $this->assertEquals($expected, $snapshotStrategy->shouldCreateSnapshot($aggregateRoot));
    }

    public static function getTestData(): array
    {
        return [
            [
                3, 2, false,
            ],
            [
                3, 5, false,
            ],
            [
                1, 4, true,
            ],
            [
                3, 3, true,
            ],
            [
                2, 4, true,
            ],
        ];
    }
}
