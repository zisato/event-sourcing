<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Identity\UUID;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\CryptoPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\DeletedKeyException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\KeyNotFoundException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;

class CryptoPrivateDataPayloadServiceTest extends TestCase
{
    private const PAYLOAD_DATA = [
        'foo' => 'fooText',
        'nested' => [
            'foo' => 'normalText',
            'bar' => 42,
        ]
    ];
    private const PAYLOAD_ENCRYPTED_DATA = [
        'foo' => 'encryptedText',
        'nested' => [
            'foo' => 'normalText',
            'bar' => 'encryptedInt'
        ]
    ];

    private PrivateDataPayloadServiceInterface $privateDataPayloadService;
    /** @var PayloadValueSerializerInterface|MockObject $payloadValueSerializer */
    private $payloadValueSerializer;
    /** @var SecretKeyStoreInterface|MockObject $secretKeyStore */
    private $secretKeyStore;
    /** @var CryptoInterface|MockObject $crypto */
    private $crypto;

    protected function setUp(): void
    {
        $this->payloadValueSerializer = $this->createMock(PayloadValueSerializerInterface::class);
        $this->secretKeyStore = $this->createMock(SecretKeyStoreInterface::class);
        $this->crypto = $this->createMock(CryptoInterface::class);

        $this->privateDataPayloadService = new CryptoPrivateDataPayloadService(
            $this->payloadValueSerializer,
            $this->secretKeyStore,
            $this->crypto
        );
    }

    public function testItShouldHideWithExistingKeySuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $secretKey = SecretKey::create('secretKey');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_DATA, $payloadKeys);
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willReturn($secretKey);

        $this->crypto->expects($this->exactly(2))
            ->method('encrypt')
            ->willReturnOnConsecutiveCalls(
                self::PAYLOAD_ENCRYPTED_DATA['foo'],
                self::PAYLOAD_ENCRYPTED_DATA['nested']['bar'],
            );

        $result = $this->privateDataPayloadService->hide($payload);

        $this->assertEquals(self::PAYLOAD_ENCRYPTED_DATA, $result);
    }

    public function testItShouldHideGeneratingKeySuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $secretKey = SecretKey::create('secretKey');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_DATA, $payloadKeys);
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willThrowException(new KeyNotFoundException());

        $this->secretKeyStore->expects($this->once())
            ->method('save')
            ->with($this->equalTo($aggregateId->value()), $this->equalTo($secretKey));

        $this->crypto->expects($this->once())
            ->method('generateSecretKey')
            ->willReturn($secretKey);

        $this->crypto->expects($this->exactly(2))
            ->method('encrypt')
            ->willReturnOnConsecutiveCalls(
                self::PAYLOAD_ENCRYPTED_DATA['foo'],
                self::PAYLOAD_ENCRYPTED_DATA['nested']['bar'],
            );

        $result = $this->privateDataPayloadService->hide($payload);

        $this->assertEquals(self::PAYLOAD_ENCRYPTED_DATA, $result);
    }

    public function testItShouldShowSuccessfully(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $secretKey = SecretKey::create('secretKey');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );

        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_ENCRYPTED_DATA, $payloadKeys);

        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willReturn($secretKey);

        $this->crypto->expects($this->exactly(2))
            ->method('decrypt')
            ->willReturnOnConsecutiveCalls(
                \json_encode(self::PAYLOAD_DATA['foo']),
                \json_encode(self::PAYLOAD_DATA['nested']['bar']),
            );
            
        $result = $this->privateDataPayloadService->show($payload);

        $expectedData = [
            'foo' => null,
            'nested' => [
                'foo' => 'normalText',
                'bar' => null,
            ]
        ];
        
        $this->assertEquals($expectedData, $result);
    }

    public function testItShouldThrowForgottedPrivateDataExceptionWhenDeletedKeyException(): void
    {
        $this->expectException(ForgottedPrivateDataException::class);
        
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_ENCRYPTED_DATA, $payloadKeys);
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willThrowException(new DeletedKeyException());
        
        $this->privateDataPayloadService->show($payload);
    }
    
    public function testItReturnSamePayloadWhenDecryptAndKeyNotFoundException(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        $payload = Payload::create($aggregateId->value(), self::PAYLOAD_ENCRYPTED_DATA, $payloadKeys);
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willThrowException(new KeyNotFoundException());

        $result = $this->privateDataPayloadService->show($payload);
        
        $this->assertEquals(self::PAYLOAD_ENCRYPTED_DATA, $result);
    }
}
