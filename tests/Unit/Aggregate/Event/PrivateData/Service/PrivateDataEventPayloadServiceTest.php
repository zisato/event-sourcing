<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

class PrivateDataEventPayloadServiceTest extends TestCase
{
    private PrivateDataEventServiceInterface $privateDataEventService;
    /** @var PayloadKeyCollectionStrategyInterface|MockObject $payloadKeyCollectionStrategy */
    private $payloadKeyCollectionStrategy;
    /** @var PrivateDataPayloadServiceInterface|MockObject $privateDataPayloadService */
    private $privateDataPayloadService;

    protected function setUp(): void
    {
        $this->payloadKeyCollectionStrategy = $this->createMock(PayloadKeyCollectionStrategyInterface::class);
        $this->privateDataPayloadService = $this->createMock(PrivateDataPayloadServiceInterface::class);

        $this->privateDataEventService = new PrivateDataEventPayloadService(
            $this->payloadKeyCollectionStrategy,
            $this->privateDataPayloadService
        );
    }

    public function testItShouldReturnSameEventWhenHideAndEmptyPayloadKeys(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $event = EventStub::occur($aggregateId->value());

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn(PayloadKeyCollection::create());

        $result = $this->privateDataEventService->hidePrivateData($event);

        $this->assertEquals($event, $result);
    }
    
    public function testItShouldReturnSameEventWhenShowAndEmptyPayloadKeys(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $event = EventStub::occur($aggregateId->value());

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn(PayloadKeyCollection::create());

        $result = $this->privateDataEventService->showPrivateData($event);

        $this->assertEquals($event, $result);
    }

    public function testItShouldHideSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $data = [
            'foo' => 'fooText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 42,
            ]
        ];
        $encryptedData = [
            'foo' => 'encryptedText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 'encryptedInt'
            ]
        ];
        $payloadKeys = PayloadKeyCollection::create(
            PayLoadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), $data, $payloadKeys);

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);

        $this->privateDataPayloadService->expects($this->once())
            ->method('hide')
            ->with($this->equalTo($payload))
            ->willReturn($encryptedData);

        $event = EventStub::occur($aggregateId->value(), $data);
        $expectedEvent = $event->reconstitute(
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $encryptedData,
            $event->version(),
            $event->metadata()
        );
        
        $result = $this->privateDataEventService->hidePrivateData($event);
    
        $this->assertEquals($expectedEvent, $result);
    }

    public function testItShouldShowSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $data = [
            'foo' => 'encryptedText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 'encryptedInt'
            ]
        ];
        $decryptedData = [
            'foo' => 'fooText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 42,
            ]
        ];
        $payloadKeys = PayloadKeyCollection::create(
            PayLoadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), $data, $payloadKeys);

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);
        
        $this->privateDataPayloadService->expects($this->once())
            ->method('show')
            ->with($this->equalTo($payload))
            ->willReturn($decryptedData);
        
        $event = EventStub::occur($aggregateId->value(), $data);
        $expectedEvent = $event->reconstitute(
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $decryptedData,
            $event->version(),
            $event->metadata()
        );
        
        $result = $this->privateDataEventService->showPrivateData($event);
    
        $this->assertEquals($expectedEvent, $result);
    }

    public function testItShouldReturnNullValuesOnShowWhenForgottedPrivateDataException(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $data = [
            'foo' => 'encryptedText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 'encryptedInt'
            ]
        ];
        $decryptedData = [
            'foo' => null,
            'nested' => [
                'foo' => 'normalText',
                'bar' => null,
            ]
        ];
        $payloadKeys = PayloadKeyCollection::create(
            PayLoadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), $data, $payloadKeys);

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);
        
        $this->privateDataPayloadService->expects($this->once())
            ->method('show')
            ->with($this->equalTo($payload))
            ->willThrowException(new ForgottedPrivateDataException());
             
        $event = EventStub::occur($aggregateId->value(), $data);
        $expectedMetadata = array_merge(
            $event->metadata(),
            [
                PrivateDataEventServiceInterface::METADATA_KEY_EVENT_FORGOTTEN_VALUES => true,
            ]
        );
        $expectedEvent = $event->reconstitute(
            $event->aggregateId(),
            $event->aggregateVersion(),
            $event->createdAt(),
            $decryptedData,
            $event->version(),
            $expectedMetadata
        );
        
        $result = $this->privateDataEventService->showPrivateData($event);

        $this->assertEquals($expectedEvent, $result);   
    }
}
