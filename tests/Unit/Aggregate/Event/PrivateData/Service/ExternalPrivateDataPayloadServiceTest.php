<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\ExternalPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Identity\IdentityInterface;

class ExternalPrivateDataPayloadServiceTest extends TestCase
{
    private const PAYLOAD_DATA = [
        'foo' => 'fooText',
        'nested' => [
            'foo' => 'normalText',
            'bar' => 42,
        ]
    ];

    private PrivateDataPayloadServiceInterface $privateDataPayloadService;
    /** @var PrivateDataRepositoryInterface|MockObject $privateDataRepository */
    private $privateDataRepository;

    protected function setUp(): void
    {
        $this->privateDataRepository = $this->createMock(PrivateDataRepositoryInterface::class);

        $this->privateDataPayloadService = new ExternalPrivateDataPayloadService(
            $this->privateDataRepository
        );
    }

    public function testItShouldHideSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );

        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_DATA, $payloadKeys);

        $this->privateDataRepository->expects($this->exactly(2))
            ->method('save');

        $result = $this->privateDataPayloadService->hide($payload);
        
        $this->assertNotEmpty($result['foo']);
        $this->assertEquals('normalText', $result['nested']['foo']);
        $this->assertNotEmpty($result['nested']['bar']);
    }


    public function testItShouldShowSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payloadEncryptedData = [
            'foo' => UUID::generate()->value(),
            'nested' => [
                'foo' => 'normalText',
                'bar' => UUID::generate()->value(),
            ]
        ];

        $payload = Payload::create($aggregateId->value(), $payloadEncryptedData, $payloadKeys);

        $this->privateDataRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                'fooText',
                42,
            );

        $result = $this->privateDataPayloadService->show($payload);

        $expectedData = [
            'foo' => 'fooText',
            'nested' => [
                'foo' => 'normalText',
                'bar' => 42,
            ]
        ];
        
        $this->assertEquals($expectedData, $result);
    }
}
