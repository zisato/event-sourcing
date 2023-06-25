<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\Identity;

use Zisato\EventSourcing\Aggregate\Identity\UUID;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zisato\EventSourcing\Aggregate\Identity\UUID
 */
class UUIDTest extends TestCase
{
    private const UUID_REGEX = '/\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}\z/Dms';

    /**
     * @dataProvider getFromStringData
     */
    public function testFromString(string $value): void
    {
        $model = UUID::fromString($value);

        $this->assertEquals($value, $model->value());
    }

    public function testGenerate(): void
    {
        $model = UUID::generate();

        $this->assertMatchesRegularExpression(self::UUID_REGEX, $model->value());
    }

    public static function getFromStringData(): array
    {
        return [
            ['33293656-6e4d-11eb-9439-0242ac130002'],
            ['961ee66a-9f99-3346-b870-c34eb90e6235'],
            ['a42cde30-aec8-45a7-ae2a-63488503fe17'],
        ];
    }
}
