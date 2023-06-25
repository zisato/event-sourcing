<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\ValueObject;

use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use PHPUnit\Framework\TestCase;

class PayloadTest extends TestCase
{
    public function testItShouldCreateSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $data = [
            'doe'=> [
                'bar' => '',
            ],
            'nested' => [
                'doe' => [
                    'bar' => '',
                ],
            ],
        ];
        $payloadKeyCollection = PayloadKeyCollection::create(
            PayloadKey::create('doe'),
            PayloadKey::create('nested', 'doe', 'bar'),
        );

        $payload = Payload::create($aggregateId->value(), $data, $payloadKeyCollection);

        $this->assertEquals($payload->payload(), $data);
    }

    public function testItShouldThrowExceptionWhenKeyNotExists(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $data = [
            'doe'=> [
                'bar' => '',
            ],
            'nested' => [
                'doe' => [
                    'bar' => '',
                ],
            ],
        ];
        $payloadKeyCollection = PayloadKeyCollection::create(
            PayloadKey::create('doe'),
            PayloadKey::create('nested', 'doe', 'foo'),
        );

        Payload::create($aggregateId->value(), $data, $payloadKeyCollection);
    }
}
