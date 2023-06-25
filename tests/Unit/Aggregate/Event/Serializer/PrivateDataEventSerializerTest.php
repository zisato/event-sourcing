<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\Serializer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\PrivateDataEventSerializer;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;

class PrivateDataEventSerializerTest extends TestCase
{
    private EventSerializerInterface $privateDataEventSerializer;
    /** @var EventSerializerInterface|MockObject $eventSerializer */
    private $eventSerializer;
    /** @var PrivateDataEventServiceInterface|MockObject $eventCryptography */
    private $privateDataEventService;

    protected function setUp(): void
    {
        $this->eventSerializer = $this->createMock(EventSerializerInterface::class);
        $this->privateDataEventService = $this->createMock(PrivateDataEventServiceInterface::class);
        $this->privateDataEventSerializer = new PrivateDataEventSerializer(
            $this->eventSerializer,
            $this->privateDataEventService
        );
    }

    public function testItShouldCreateFromArraySuccessfully(): void
    {
        $data = [
            'foo' => 'bar'
        ];
        /** @var EventInterface|MockObject $event */
        $event = $this->createMock(EventInterface::class);

        $this->eventSerializer
            ->expects($this->once())
            ->method('fromArray')
            ->with($this->equalTo($data))
            ->willReturn($event);

        $this->privateDataEventService
            ->expects($this->once())
            ->method('showPrivateData')
            ->with($this->equalTo($event))
            ->willReturn($event);

        $result = $this->privateDataEventSerializer->fromArray($data);

        $this->assertEquals($event, $result);
    }

    public function testItShouldReturnArraySuccessfully(): void
    {
        $data = [
            'foo' => 'bar'
        ];
        /** @var EventInterface|MockObject $event */
        $event = $this->createMock(EventInterface::class);

        $this->privateDataEventService
            ->expects($this->once())
            ->method('hidePrivateData')
            ->with($this->equalTo($event))
            ->willReturn($event);

        $this->eventSerializer
            ->expects($this->once())
            ->method('toArray')
            ->with($this->equalTo($event))
            ->willReturn($data);

        $result = $this->privateDataEventSerializer->toArray($event);

        $this->assertEquals($data, $result);
    }
}
