<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Serializer;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Exception\AggregateSerializerException;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Serializer\AggregateRootSerializerInterface;
use Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootSerializer;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootSerializer
 */
class ReflectionAggregateRootSerializerTest extends TestCase
{
    private AggregateRootSerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ReflectionAggregateRootSerializer();
    }

    public function testItShouldSerializeAndDeserializeSucessfully(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');

        $aggregate = AggregateRootStub::fromIdentity($aggregateId);

        $serialized = $this->serializer->serialize($aggregate);

        $result = $this->serializer->deserialize($serialized);

        $this->assertEquals($aggregate, $result);
    }

    public function testItShouldThrowExceptionWhenNotEmptyRecordedEvents(): void
    {
        $this->expectException(AggregateSerializerException::class);

        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregate = AggregateRootStub::fromEvent($aggregateId, $event);

        $this->serializer->serialize($aggregate);
    }
    
    public function testItShouldThrowExceptionWhenClassNotImplementsAggregateRoot(): void
    {
        $this->expectException(AggregateSerializerException::class);

        $data = 'a:2:{s:10:"class_name";s:57:"Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub";s:10:"properties";a:0:{}}';

        $this->serializer->deserialize($data);
    }
    
    public function testItShouldThrowExceptionWhenNonExistingClassNameKey(): void
    {
        $this->expectException(AggregateSerializerException::class);

        $data = 'a:2:{s:27:"non_existing_key_class_name";s:3:"foo";s:10:"properties";a:0:{}}';

        $this->serializer->deserialize($data);
    }
    
    public function testItShouldThrowExceptionWhenNonExistingPropertiesKey(): void
    {
        $this->expectException(AggregateSerializerException::class);

        $data = 'a:2:{s:10:"class_name";s:3:"foo";s:27:"non_existing_key_properties";a:0:{}}';

        $this->serializer->deserialize($data);
    }
}
