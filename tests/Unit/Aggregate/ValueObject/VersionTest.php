<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\Tests\Unit\Aggregate\ValueObject;

use Zisato\EventSourcing\Aggregate\ValueObject\Version;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Zisato\EventSourcing\Aggregate\ValueObject\Version
 */
class VersionTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreate(int $value): void
    {
        $model = Version::create($value);

        $this->assertEquals($value, $model->value());
    }

    /**
     * @dataProvider getCreateExceptionData
     */
    public function testCreateException(int $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Version::create($value);
    }

    public function testZero(): void
    {
        $model = Version::zero();

        $this->assertEquals(0, $model->value());
    }

    /**
     * @dataProvider getNextData
     */
    public function testNext(int $current, int $expected): void
    {
        $model = Version::create($current);

        $this->assertEquals($expected, $model->next()->value());
    }

    /**
     * @dataProvider getEqualsData
     */
    public function testEquals(int $current, int $next, bool $expected): void
    {
        $currentVersion = Version::create($current);
        $nextVersion = Version::create($next);

        $this->assertEquals($currentVersion->equals($nextVersion), $expected);
    }

    public static function getCreateData(): array
    {
        return [
            [0],
            [1],
            [2],
        ];
    }

    public static function getCreateExceptionData(): array
    {
        return [
            [-1],
        ];
    }

    public static function getNextData(): array
    {
        return [
            [0, 1],
            [4, 5],
            [100, 101],
        ];
    }

    public static function getEqualsData(): array
    {
        return [
            [0, 1, false],
            [5, 4, false],
            [1, 1, true],
            [42, 42, true],
        ];
    }
}
