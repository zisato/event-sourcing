<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Event;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Event\Event;

/**
 * @covers \Zisato\EventSourcing\Event\Event
 */
class EventTest extends TestCase
{
    /**
     * @dataProvider getConstructData
     */
    public function testConstruct(DateTimeImmutable $createdAt, array $payload): void
    {
        $model = new Event($createdAt, $payload);

        $this->assertEquals($createdAt, $model->createdAt());
        $this->assertEquals($payload, $model->payload());
    }

    public static function getConstructData(): array
    {
        return [
            [
                new DateTimeImmutable(),
                []
            ],
            [
                new DateTimeImmutable(),
                ['foo' => 'bar']
            ]
        ];
    }
}
