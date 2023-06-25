<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Decorator;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\Decorator\NullEventDecorator;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Decorator\NullEventDecorator
 */
class NullEventDecoratorTest extends TestCase
{
    /**
     * @dataProvider getDecorateData
     */
    public function testDecorate(EventInterface $event, EventInterface $expectedEvent): void
    {
        $eventDecorator = new NullEventDecorator();

        $result = $eventDecorator->decorate($event);

        $this->assertEquals($result, $expectedEvent);
    }

    public static function getDecorateData(): array
    {
        $aggregateId = '2c2f0530-f3fb-11ec-b939-0242ac120002';
        $event = EventStub::occur($aggregateId);

        return [
            [
                $event,
                $event,
            ],
        ];
    }
}
