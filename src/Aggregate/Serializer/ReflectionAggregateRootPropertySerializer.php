<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Serializer;

use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

class ReflectionAggregateRootPropertySerializer
{
    private const PROPERTIES_VISIBILITY = \ReflectionProperty::IS_PUBLIC |
        \ReflectionProperty::IS_PROTECTED |
        \ReflectionProperty::IS_PRIVATE;

    /**
     * @param \ReflectionClass<object> $class
     * @return array<string, mixed>
     */
    public function getProperties(AggregateRootInterface $aggregateRoot, \ReflectionClass $class): array
    {
        $result = [];
        $properties = $class->getProperties(self::PROPERTIES_VISIBILITY);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $result[$property->getName()] = $property->getValue($aggregateRoot);
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param array<string, mixed> $propertiesValues
     */
    public function setProperties(
        AggregateRootInterface $aggregateRoot,
        \ReflectionClass $class,
        array $propertiesValues
    ): void {
        $properties = $class->getProperties(self::PROPERTIES_VISIBILITY);

        foreach ($properties as $property) {
            $name = $property->getName();

            if ($this->propertyExists($name, $propertiesValues)) {
                $property->setAccessible(true);
                $property->setValue($aggregateRoot, $propertiesValues[$name]);
            }
        }
    }

    /**
     * @param array<string, mixed> $propertiesValues
     */
    private function propertyExists(string $name, array $propertiesValues): bool
    {
        return \array_key_exists($name, $propertiesValues);
    }
}
