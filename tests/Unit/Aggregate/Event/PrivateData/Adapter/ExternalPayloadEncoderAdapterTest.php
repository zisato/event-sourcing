<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Identity\UUID;

class ExternalPayloadEncoderAdapterTest extends TestCase
{
    private const PAYLOAD_DATA = [
        'foo' => 'fooText',
        'nested' => [
            'foo' => 'normalText',
            'bar' => 42,
        ]
    ];

    private PayloadEncoderAdapterInterface $payloadEncoderAdapter;
    /** @var PrivateDataRepositoryInterface|MockObject $privateDataRepository */
    private $privateDataRepository;

    protected function setUp(): void
    {
        $this->privateDataRepository = $this->createMock(PrivateDataRepositoryInterface::class);

        $this->payloadEncoderAdapter = new ExternalPayloadEncoderAdapter(
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

        $this->privateDataRepository->expects($this->exactly(2))
            ->method('save');

        $result = $this->payloadEncoderAdapter->hide($aggregateId->value(), $payloadKeys, self::PAYLOAD_DATA);
        
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

        $this->privateDataRepository->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                'fooText',
                42,
            );

        $result = $this->payloadEncoderAdapter->show($aggregateId->value(), $payloadKeys, $payloadEncryptedData);

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
