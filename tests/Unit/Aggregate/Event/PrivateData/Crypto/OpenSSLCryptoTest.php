<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\Crypto;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\OpenSSLCrypto;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\OpenSSLCrypto
 */
class OpenSSLCryptoTest extends TestCase
{
    private CryptoInterface $crypto;

    protected function setUp(): void
    {
        $this->crypto = new OpenSSLCrypto();
    }

    public function testItShouldCreateKeySuccessfully(): void
    {
        $key = $this->crypto->generateSecretKey();

        $this->assertNotEmpty($key->value());
    }

    public function testItShouldDecryptSuccessfully(): void
    {
        $encrypted = 'z2l02s/PIYcvqbAQ25HLYWwwQkxRUWNNUEtrOWpwYlpqOWVzT2c9PQ==';
        $secretKey = SecretKey::create('TestKey');

        $result = $this->crypto->decrypt($encrypted, $secretKey);
        $expected = 'Test message';

        $this->assertEquals($expected, $result);
    }
}
