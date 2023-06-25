<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\Serializer;

use DateTimeImmutable;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\JSON\JSON;

final class EventSerializer implements EventSerializerInterface
{
    public function __construct(private readonly VersionResolverInterface $versionResolver)
    {
    }

    public function fromArray(array $data): EventInterface
    {
        /** @var callable $callable */
        $callable = [$data['event_class'], 'reconstitute'];

        return \call_user_func(
            $callable,
            $data['aggregate_id'],
            $data['aggregate_version'],
            new DateTimeImmutable($data['created_at']),
            JSON::decode($data['payload']),
            $data['version'],
            JSON::decode($data['metadata']),
        );
    }

    /**
     * @return array{event_class: class-string<\Zisato\EventSourcing\Aggregate\Event\EventInterface>&string, aggregate_id: string, aggregate_version: int, created_at: string, payload: string, version: int, metadata: string}
     */
    public function toArray(EventInterface $event): array
    {
        return [
            'event_class' => $event::class,
            'aggregate_id' => $event->aggregateId(),
            'aggregate_version' => $event->aggregateVersion(),
            'created_at' => $event->createdAt()
                ->format(static::DATE_FORMAT),
            'payload' => JSON::encode($event->payload()),
            'version' => $this->versionResolver->resolve($event),
            'metadata' => JSON::encode($event->metadata()),
        ];
    }
}
