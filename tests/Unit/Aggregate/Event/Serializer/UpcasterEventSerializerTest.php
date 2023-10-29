<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Serializer;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\UpcasterEventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;
use Zisato\EventSourcing\JSON\JSON;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Serializer\UpcasterEventSerializer
 */
class UpcasterEventSerializerTest extends TestCase
{
    private EventSerializerInterface|MockObject $eventSerializer;
    private UpcasterInterface|MockObject $upcaster;
    private UpcasterEventSerializer $upcasterEventSerializer;

    protected function setUp(): void
    {
        $this->eventSerializer = $this->createMock(EventSerializerInterface::class);
        $this->upcaster = $this->createMock(UpcasterInterface::class);

        $this->upcasterEventSerializer = new UpcasterEventSerializer(
            $this->eventSerializer,
            $this->upcaster
        );
    }

    /**
     * @dataProvider getFromArrayData
     */
    public function testFromArray(array $data): void
    {
        $aggregateId = $data['aggregate_id'];
        $aggregateVersion = $data['aggregate_version'];

        $event = $this->createMock(EventStub::class);

        $eventUpcasted = EventStub::reconstitute(
            $aggregateId,
            $aggregateVersion,
            $createdAt = new DateTimeImmutable(),
            $payload = ['foo' => 'bar'],
            $version = 2,
            $metadata = ['key' => 'value']
        );

        $this->eventSerializer->expects($this->once())
            ->method('fromArray')
            ->with($this->equalTo($data))
            ->willReturn($event);

        $this->upcaster->expects($this->once())
            ->method('canUpcast')
            ->with($this->equalTo($event))
            ->willReturn(true);

        $this->upcaster->expects($this->once())
            ->method('upcast')
            ->with($this->equalTo($event))
            ->willReturn($eventUpcasted);

        $result = $this->upcasterEventSerializer->fromArray($data);

        $this->assertEquals($aggregateId, $result->aggregateId());
        $this->assertEquals($aggregateVersion, $result->aggregateVersion());
        $this->assertEquals($createdAt, $result->createdAt());
        $this->assertEquals($version, $result->version());
        $this->assertEquals($payload, $result->payload());
        $this->assertEquals($metadata, $result->metadata());
    }

    public function testFromArrayWhenNoUpcaster(): void
    {
        $data = [
            'event_class' => EventStub::class,
            'aggregate_id' => '2c2f0530-f3fb-11ec-b939-0242ac120002',
            'aggregate_version' => 1,
            'created_at' => (new DateTimeImmutable())->format(EventSerializerInterface::DATE_FORMAT),
            'payload' => JSON::encode([]),
            'version' => 2,
            'metadata' => JSON::encode(['key' => 'value'])
        ];

        $event = $this->createMock(EventStub::class);

        $this->eventSerializer->expects($this->once())
            ->method('fromArray')
            ->with($this->equalTo($data))
            ->willReturn($event);

        $this->upcaster->expects($this->once())
            ->method('canUpcast')
            ->with($this->equalTo($event))
            ->willReturn(false);

        $this->upcaster->expects($this->never())
            ->method('upcast');

        $result = $this->upcasterEventSerializer->fromArray($data);

        $this->assertEquals($event, $result);
    }

    /**
     * @dataProvider getToArrayData
     */
    public function testToArray(EventInterface $event): void
    {
        $data = [
            'event_class' => get_class($event),
            'aggregate_id' => $event->aggregateId(),
            'aggregate_version' => $event->version(),
            'created_at' => $event->createdAt()->format(EventSerializerInterface::DATE_FORMAT),
            'payload' => JSON::encode($event->payload()),
            'version' => $event->version(),
            'metadata' => JSON::encode($event->metadata())
        ];

        $this->eventSerializer->expects($this->once())
            ->method('toArray')
            ->with($this->equalTo($event))
            ->willReturn($data);

        $result = $this->upcasterEventSerializer->toArray($event);

        $this->assertEquals($result, $data);
    }
    
    public static function getFromArrayData(): array
    {
        return [
            [
                [
                    'event_class' => EventStub::class,
                    'aggregate_id' => '2c2f0530-f3fb-11ec-b939-0242ac120002',
                    'aggregate_version' => 1,
                    'created_at' => (new DateTimeImmutable())->format(EventSerializerInterface::DATE_FORMAT),
                    'payload' => JSON::encode([]),
                    'version' => 2,
                    'metadata' => JSON::encode(['key' => 'value'])
                ]
            ]
        ];
    }
    
    public static function getToArrayData(): array
    {
        return [
            [
                EventStub::reconstitute(
                    '2c2f0530-f3fb-11ec-b939-0242ac120002',
                    1,
                    new DateTimeImmutable(),
                    [],
                    1,
                    ['key' => 'value'],
                )
            ]
        ];
    }
}
