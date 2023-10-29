<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Strategy;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\PrivateDataPayloadInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionByEventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\PrivateDataPayloadStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionByEventInterface
 */
class PayloadKeyCollectionByEventInterfaceTest extends TestCase
{
    private PayloadKeyCollectionByEventInterface $payloadKeyCollectionStrategy;

    protected function setUp(): void
    {
        $this->payloadKeyCollectionStrategy = new PayloadKeyCollectionByEventInterface();
    }

    public function testItShouldReturnKeysSuccessfully(): void
    {
        $expected = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('bar', 'doe')
        );
        /** @var EventInterface|PrivateDataPayloadInterface|MockObject $event */
        $event = $this->createMock(PrivateDataPayloadStub::class);
        $event->expects($this->once())
            ->method('privateDataPayloadKeys')
            ->willReturn($expected);

        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        $this->assertEquals($expected, $payloadKeys);
    }

    public function testItShouldReturnEmptyCollectionWhenNotInstance(): void
    {
        $expected = PayloadKeyCollection::create();
        /** @var EventInterface|MockObject $event */
        $event = $this->createMock(EventInterface::class);

        $payloadKeys = $this->payloadKeyCollectionStrategy->payloadKeys($event);

        $this->assertEquals($expected, $payloadKeys);
    }
}
