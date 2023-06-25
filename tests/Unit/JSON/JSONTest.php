<?php

namespace Zisato\EventSourcing\Tests\Unit\JSON;

use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\JSON\JSON;

/**
 * @covers \Zisato\EventSourcing\JSON\JSON
 */
class JSONTest extends TestCase
{
    /**
     * @dataProvider getEncodeData
     */
    public function testEncode(array $payload, string $expected): void
    {
        $result = JSON::encode($payload);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getDecodeData
     */
    public function testDecode(string $payload, array $expected): void
    {
        $result = JSON::decode($payload);

        $this->assertEquals($expected, $result);
    }

    public static function getEncodeData(): array
    {
        return [
            [
                [
                    'foo' => 'bar',
                    'jhon' => [
                        'doe' => 1,
                    ]
                ],
                '{"foo":"bar","jhon":{"doe":1}}',
            ]
        ];
    }

    public static function getDecodeData(): array
    {
        return [
            [
                '{"foo":"bar","jhon":{"doe":1}}',
                [
                    'foo' => 'bar',
                    'jhon' => [
                        'doe' => 1,
                    ]
                ]
            ]
        ];
    }
}
