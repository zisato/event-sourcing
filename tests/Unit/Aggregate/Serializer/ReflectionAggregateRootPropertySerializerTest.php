<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Serializer;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootPropertySerializer;
use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use Zisato\EventSourcing\Tests\Stub\Aggregate\AggregateRootStub;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootPropertySerializer
 */
class ReflectionAggregateRootPropertySerializerTest extends TestCase
{
    private ReflectionAggregateRootPropertySerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ReflectionAggregateRootPropertySerializer();
    }

    public function testItShouldGetAggregateRootProperties(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);
        /** @var \ReflectionClass<AggregateRoot> $class */
        $class = new \ReflectionClass($aggregateRoot);

        $result = $this->serializer->getProperties($aggregateRoot, $class->getParentClass());

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('recordedEvents', $result);
    }

    public function testItShuldSetExistingProperties(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);
        
        /** @var \ReflectionClass<AggregateRoot> $class */
        $class = new \ReflectionClass($aggregateRoot);

        $propertiesValues = [
            'version' => Version::create(2),
        ];

        $this->serializer->setProperties($aggregateRoot, $class->getParentClass(), $propertiesValues);

        $this->assertEquals(2, $aggregateRoot->version()->value());
    }

    public function testItShouldIgnoreNotExistingPropertyAggregateRootInData(): void
    {
        $aggregateId = UUID::fromString('e21f9b3c-8446-11eb-855e-0242ac120002');
        $event = EventStub::occur($aggregateId->value());
        $aggregateRoot = AggregateRootStub::fromEvent($aggregateId, $event);
        
        /** @var \ReflectionClass<AggregateRoot> $class */
        $class = new \ReflectionClass($aggregateRoot);

        $propertiesValues = [
            'nonExisting' => 'value'
        ];

        $this->serializer->setProperties($aggregateRoot, $class->getParentClass(), $propertiesValues);

        $this->assertClassNotHasAttribute('nonExisting', $aggregateRoot);
    }

    private function assertClassNotHasAttribute(string $attributeName, $object): void
    {
        $this->assertIsObject($object);
        $this->assertFalse(property_exists($object, $attributeName));
    }
}
