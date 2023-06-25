<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Serializer;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Exception\AggregateSerializerException;

class ReflectionAggregateRootSerializer implements AggregateRootSerializerInterface
{
    private const KEY_CLASS_NAME = 'class_name';

    private const KEY_PROPERTIES = 'properties';

    private const REQUIRED_KEYS = [self::KEY_CLASS_NAME, self::KEY_PROPERTIES];

    private ReflectionAggregateRootPropertySerializer $reflectionAggregateRootPropertySerializer;

    public function __construct()
    {
        $this->reflectionAggregateRootPropertySerializer = new ReflectionAggregateRootPropertySerializer();
    }

    public function serialize(AggregateRootInterface $aggregateRoot): string
    {
        $this->assertNotEmptyRecordedEvents($aggregateRoot);

        $result = [
            self::KEY_CLASS_NAME => \get_class($aggregateRoot),
            self::KEY_PROPERTIES => $this->getProperties($aggregateRoot),
        ];

        return \serialize($result);
    }

    public function deserialize(string $aggregateRoot): AggregateRootInterface
    {
        $data = \unserialize($aggregateRoot);

        $this->assertRequiredKeys($data);

        /** @var \ReflectionClass<AggregateRootInterface> $class */
        $class = new \ReflectionClass($data[self::KEY_CLASS_NAME]);

        $this->assertNotImplementsAggregateRoot($class, $data[self::KEY_CLASS_NAME]);

        /** @var AggregateRootInterface $instance */
        $instance = $class->newInstanceWithoutConstructor();

        $this->setProperties($instance, $class, $data[self::KEY_PROPERTIES]);

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    private function getProperties(AggregateRootInterface $aggregateRoot): array
    {
        $result = [];
        /** @var \ReflectionClass<AggregateRootInterface> $class */
        $class = new \ReflectionClass($aggregateRoot);

        do {
            $properties = $this->reflectionAggregateRootPropertySerializer->getProperties($aggregateRoot, $class);

            $result[$class->getName()] = $properties;
        } while ($class = $class->getParentClass());

        return $result;
    }

    /**
     * @param \ReflectionClass<AggregateRootInterface> $class
     * @param array<string, mixed> $propertiesData
     */
    private function setProperties(
        AggregateRootInterface $aggregateRoot,
        \ReflectionClass $class,
        array $propertiesData
    ): void {
        do {
            $propertiesValues = $propertiesData[$class->getName()];

            $this->reflectionAggregateRootPropertySerializer->setProperties(
                $aggregateRoot,
                $class,
                $propertiesValues
            );
        } while ($class = $class->getParentClass());
    }

    private function assertNotEmptyRecordedEvents(AggregateRootInterface $aggregateRoot): void
    {
        if ($aggregateRoot->hasRecordedEvents()) {
            throw new AggregateSerializerException('Cannot serialize AggregateRoot with recorded events');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function assertRequiredKeys(array $data): void
    {
        foreach (self::REQUIRED_KEYS as $key) {
            if (! \array_key_exists($key, $data)) {
                throw new AggregateSerializerException(\sprintf(
                    'Key %s must be provided in unserialize aggregate data',
                    $key
                ));
            }
        }
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function assertNotImplementsAggregateRoot(\ReflectionClass $class, string $className): void
    {
        if (! $class->implementsInterface(AggregateRootInterface::class)) {
            throw new AggregateSerializerException(\sprintf(
                'Class %s must implement %s',
                $className,
                AggregateRootInterface::class
            ));
        }
    }
}
