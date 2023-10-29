<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Adapter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\CryptoPayloadEncoderAdapter;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\DeletedKeyException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\ForgottedPrivateDataException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Exception\KeyNotFoundException;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKey;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;
use Zisato\EventSourcing\Aggregate\Identity\UUID;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\CryptoPayloadEncoderAdapter
 */
class CryptoPayloadEncoderAdapterTest extends TestCase
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

    private PayloadEncoderAdapterInterface $payloadEncoderAdapter;
    private PayloadValueSerializerInterface|MockObject $payloadValueSerializer;
    private SecretKeyStoreInterface|MockObject $secretKeyStore;
    private CryptoInterface|MockObject $crypto;

    protected function setUp(): void
    {
        $this->payloadValueSerializer = $this->createMock(PayloadValueSerializerInterface::class);
        $this->secretKeyStore = $this->createMock(SecretKeyStoreInterface::class);
        $this->crypto = $this->createMock(CryptoInterface::class);

        $this->payloadEncoderAdapter = new CryptoPayloadEncoderAdapter(
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
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willReturn($secretKey);

        $this->crypto->expects($this->exactly(2))
            ->method('encrypt')
            ->willReturnOnConsecutiveCalls(
                self::PAYLOAD_ENCRYPTED_DATA['foo'],
                self::PAYLOAD_ENCRYPTED_DATA['nested']['bar'],
            );

        $result = $this->payloadEncoderAdapter->hide($aggregateId->value(), $payloadKeys, self::PAYLOAD_DATA);

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

        $result = $this->payloadEncoderAdapter->hide($aggregateId->value(), $payloadKeys, self::PAYLOAD_DATA);

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

        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willReturn($secretKey);

        $this->crypto->expects($this->exactly(2))
            ->method('decrypt')
            ->willReturnOnConsecutiveCalls(
                \json_encode(self::PAYLOAD_DATA['foo']),
                \json_encode(self::PAYLOAD_DATA['nested']['bar']),
            );
            
        $result = $this->payloadEncoderAdapter->show($aggregateId->value(), $payloadKeys, self::PAYLOAD_ENCRYPTED_DATA);

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
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willThrowException(new DeletedKeyException());
        
        $this->payloadEncoderAdapter->show($aggregateId->value(), $payloadKeys, self::PAYLOAD_ENCRYPTED_DATA);
    }
    
    public function testItReturnSamePayloadWhenDecryptAndKeyNotFoundException(): void
    {
        $aggregateId = UUID::fromString('022390a2-f596-11ec-b939-0242ac120002');
        $payloadKeys = PayloadKeyCollection::create(
            PayloadKey::create('foo'),
            PayloadKey::create('nested', 'bar')
        );
        
        $this->secretKeyStore->expects($this->once())
            ->method('get')
            ->willThrowException(new KeyNotFoundException());

        $result = $this->payloadEncoderAdapter->show($aggregateId->value(), $payloadKeys, self::PAYLOAD_ENCRYPTED_DATA);
        
        $this->assertEquals(self::PAYLOAD_ENCRYPTED_DATA, $result);
    }
}
