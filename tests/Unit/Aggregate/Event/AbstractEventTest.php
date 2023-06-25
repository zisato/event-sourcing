<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Tests\Stub\Aggregate\Event\EventStub;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\AbstractEvent
 */
class AbstractEventTest extends TestCase
{
    /**
     * @dataProvider getReconstituteData
     */
    public function testReconstitute(
        string $aggregateId,
        int $aggregateVersion,
        DateTimeImmutable $createdAt,
        array $payload,
        int $version,
        array $metadata
    ): void
    {
        $model = EventStub::reconstitute(
            $aggregateId,
            $aggregateVersion,
            $createdAt,
            $payload,
            $version,
            $metadata
        );

        $this->assertEquals($aggregateId, $model->aggregateId());
        $this->assertEquals($aggregateVersion, $model->aggregateVersion());
        $this->assertEquals($createdAt, $model->createdAt());
        $this->assertEquals($payload, $model->payload());
        $this->assertEquals($version, $model->version());
        $this->assertEquals($metadata, $model->metadata());
    }

    /**
     * @dataProvider getOccurData
     */
    public function testOccur(string $aggregateId, array $payload): void
    {
        $model = EventStub::occur($aggregateId, $payload);

        $this->assertEquals($aggregateId, $model->aggregateId());
        $this->assertEquals($payload, $model->payload());
    }

    /**
     * @dataProvider getWithAggregateVersionData
     */
    public function testWithAggregateVersion(int $aggregateVersion): void
    {
        $model = EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002');

        $model = $model->withAggregateVersion($aggregateVersion);

        $this->assertEquals($aggregateVersion, $model->aggregateVersion());
    }

    /**
     * @dataProvider getAddMetadataData
     */
    public function testAddMetadata(array $metadata): void
    {
        $model = EventStub::occur('2c2f0530-f3fb-11ec-b939-0242ac120002');

        foreach ($metadata as $key => $value) {
            $model = $model->withMetadata($key, $value);
        }

        $this->assertEquals($metadata, $model->metadata());
    }

    public static function getReconstituteData(): array
    {
        return [
            [
                '2c2f0530-f3fb-11ec-b939-0242ac120002',
                1,
                new DateTimeImmutable(),
                [
                    'name' => 'personName',
                    'email' => 'person@email.com',
                    'phone' => '123456789',
                ],
                1,
                [
                    'foo' => 'bar'
                ]
            ]
        ];
    }

    public static function getOccurData(): array
    {
        return [
            [
                '2c2f0530-f3fb-11ec-b939-0242ac120002',
                [
                    'foo' => 'bar'
                ]
            ]
        ];
    }

    public static function getWithAggregateVersionData(): array
    {
        return [
            [
                3
            ]
        ];
    }

    public static function getAddMetadataData(): array
    {
        return [
            [
                [
                    'bool' => true,
                    'int' => 42,
                    'float' => 23.5,
                    'string' => 'foo'
                ]
            ]
        ];
    }
}
