<?php

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Event\PrivateData\ValueObject;

use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\SecretKey;
use PHPUnit\Framework\TestCase;

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
