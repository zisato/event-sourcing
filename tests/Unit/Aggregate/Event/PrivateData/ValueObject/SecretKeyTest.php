<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\ValueObject;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey
 */
class SecretKeyTest extends TestCase
{
    public function testItShouldCreateSuccessfully(): void
    {
        $value = 'secretKeyTest';

        $secretKey = SecretKey::create($value);

        $this->assertEquals($secretKey->value(), 'secretKeyTest');
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testItShouldThrowExceptionWhenInvalidValue(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        SecretKey::create($value);
    }

    public static function getInvalidData(): array
    {
        return [
            [
                ''
            ],
            [
                ' '
            ]
        ];
    }
}
