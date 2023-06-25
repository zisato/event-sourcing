<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Serializer;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\JsonPayloadValueSerializer;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;

class JsonPayloadValueSerializerTest extends TestCase
{
    private PayloadValueSerializerInterface $payloadValueSerializer;

    protected function setUp(): void
    {
        $this->payloadValueSerializer = new JsonPayloadValueSerializer();
    }

    public function testItShouldReturnStringSuccessfully(): void
    {
        $payload = [
            'foo' => 'bar',
            'jhon' => [
                'doe' => 1,
            ]
        ];

        $result = $this->payloadValueSerializer->toString($payload);

        $this->assertEquals($result, \json_encode($payload, \JSON_UNESCAPED_UNICODE));
    }

    public function testItShouldReturnValueSuccessfully(): void
    {
        $payload = '{"foo":"bar","jhon":{"doe":1}}';

        $result = $this->payloadValueSerializer->fromString($payload);

        $this->assertEquals($result, \json_decode($payload, true));
    }
}
