<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

class PrivateDataEventPayloadServiceTest extends TestCase
{
    private PrivateDataEventServiceInterface $privateDataEventService;
    /** @var PayloadKeyCollectionStrategyInterface|MockObject $payloadKeyCollectionStrategy */
    private $payloadKeyCollectionStrategy;
    /** @var PayloadEncoderAdapterInterface|MockObject $payloadEncoderAdapter */
    private $payloadEncoderAdapter;

    protected function setUp(): void
    {
        $this->payloadKeyCollectionStrategy = $this->createMock(PayloadKeyCollectionStrategyInterface::class);
        $this->payloadEncoderAdapter = $this->createMock(PayloadEncoderAdapterInterface::class);

        $this->privateDataEventService = new PrivateDataEventService(
            $this->payloadKeyCollectionStrategy,
            $this->payloadEncoderAdapter
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

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);

        $this->payloadEncoderAdapter->expects($this->once())
            ->method('hide')
            ->with($this->equalTo($aggregateId->value()), $this->equalTo($payloadKeys), $this->equalTo($data))
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

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);
        
        $this->payloadEncoderAdapter->expects($this->once())
            ->method('show')
            ->with($this->equalTo($aggregateId->value()), $this->equalTo($payloadKeys), $this->equalTo($data))
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

        $this->payloadKeyCollectionStrategy->expects($this->once())
            ->method('payloadKeys')
            ->willReturn($payloadKeys);
        
        $this->payloadEncoderAdapter->expects($this->once())
            ->method('show')
            ->with($this->equalTo($aggregateId->value()), $this->equalTo($payloadKeys), $this->equalTo($data))
            ->willThrowException(new ForgottedPrivateDataException());

        $this->payloadEncoderAdapter->expects($this->once())
            ->method('forget')
            ->with($this->equalTo($aggregateId->value()), $this->equalTo($payloadKeys), $this->equalTo($data))
            ->willReturn($decryptedData);

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
