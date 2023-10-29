<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Serializer;

use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\JSON\JSON;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializer
 */
class EventSerializerTest extends TestCase
{
    private VersionResolverInterface|MockObject $versionResolver;
    private EventSerializer $eventSerializer;

    protected function setUp(): void
    {
        $this->versionResolver = $this->createMock(VersionResolverInterface::class);
        $this->eventSerializer = new EventSerializer(
            $this->versionResolver
        );
    }

    /**
     * @dataProvider getFromArrayData
     */
    public function testFromArray(array $data): void
    {
        $this->versionResolver->expects($this->never())
            ->method('resolve');

        $result = $this->eventSerializer->fromArray($data);

        $this->assertEquals($data['aggregate_id'], $result->aggregateId());
        $this->assertEquals($data['aggregate_version'], $result->aggregateVersion());
        $this->assertEquals($data['created_at'], $result->createdAt()->format(EventSerializerInterface::DATE_FORMAT));
        $this->assertEquals(JSON::decode($data['payload']), $result->payload());
        $this->assertEquals($data['version'], $result->version());
        $this->assertEquals(JSON::decode($data['metadata']), $result->metadata());
    }

    /**
     * @dataProvider getToArrayData
     */
    public function testToArray(EventInterface $event): void
    {
        $this->versionResolver->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo($event))
            ->willReturn($event->version());

        $result = $this->eventSerializer->toArray($event);

        $this->assertEquals(EventStub::class, $result['event_class']);
        $this->assertEquals($event->aggregateId(), $result['aggregate_id']);
        $this->assertEquals($event->aggregateVersion(), $result['aggregate_version']);
        $this->assertEquals($event->createdAt()->format(EventSerializerInterface::DATE_FORMAT), $result['created_at']);
        $this->assertEquals(JSON::encode($event->payload()), $result['payload']);
        $this->assertEquals($event->version(), $result['version']);
        $this->assertEquals(JSON::encode($event->metadata()), $result['metadata']);
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
                    'metadata' => JSON::encode([])
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
                    3,
                    [],
                )
            ]
        ];
    }
}
