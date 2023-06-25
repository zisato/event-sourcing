<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Upcast;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\EventUpcasterChain;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventWithVersionStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Upcast\EventStubUpcasterFom1To2Stub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\Upcast\EventWithVersionStubUpcasterFom3To4Stub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Upcast\EventUpcasterChain
 */
class EventUpcasterChainTest extends TestCase
{
    /**
     * @dataProvider getUpcastData
     */
    public function testUpcast(EventInterface $event, array $expectedData, int $expectedVersion): void
    {
        $upcaster1 = new EventStubUpcasterFom1To2Stub();
        $upcaster2 = new EventWithVersionStubUpcasterFom3To4Stub();

        $upcasterChain = new EventUpcasterChain($upcaster2, $upcaster1);

        $canUpcast = $upcasterChain->canUpcast($event);
        $event = $upcasterChain->upcast($event);

        $this->assertTrue($canUpcast);
        $this->assertEquals($expectedVersion, $event->version());

        foreach ($expectedData as $key => $expectedValue) {
            $this->assertEquals($expectedValue, $event->payload()[$key]);
        }
    }

    /**
     * @dataProvider getCanUpcastFalseData
     */
    public function testCanUpcastFalse(EventInterface $event, UpcasterInterface $upcaster): void
    {
        $result = $upcaster->canUpcast($event);

        $this->assertFalse($result);
    }

    public static function getUpcastData(): array
    {
        return [
            [
                
                EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002'),
                [
                    'upcaster_stub_1_to_2' => true,
                ],
                2
            ],
            [
                EventWithVersionStub::reconstitute(
                    '34e13904-f3fc-11ec-b939-0242ac120002',
                    0,
                    new DateTimeImmutable(),
                    [],
                    3,
                    []
                ),
                [
                    'upcaster_stub_3_to_4' => true,
                ],
                4
            ],
        ];
    }

    public static function getCanUpcastFalseData(): array
    {
        return [
            [
                EventWithVersionStub::reconstitute(
                    '2c2f0530-f3fb-11ec-b939-0242ac120002',
                    0,
                    new DateTimeImmutable(),
                    [],
                    3,
                    []
                ),
                new EventUpcasterChain(new EventStubUpcasterFom1To2Stub()),
            ],
            [
                EventStub::occur('34e13904-f3fc-11ec-b939-0242ac120002'),
                new EventUpcasterChain(),
            ],
        ];
    }
}
