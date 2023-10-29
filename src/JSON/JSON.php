<?php

declare(strict_types=1);

namespace Zisato\EventSourcing\JSON;

final class JSON
{
    /**
     * @var int
     */
    private const JSON_DEPTH = 512;

    /**
     * @var int
     */
    private const JSON_DECODE_OPTIONS = \JSON_THROW_ON_ERROR;

    /**
     * @var int
     */
    private const JSON_ENCODE_OPTIONS = \JSON_UNESCAPED_UNICODE |
        \JSON_THROW_ON_ERROR;

    public static function encode(mixed $data): string
    {
        return \json_encode($data, self::JSON_ENCODE_OPTIONS);
    }

    /**
     * @return mixed
     */
    public static function decode(string $data, bool $assoc = true)
    {
        return \json_decode($data, $assoc, self::JSON_DEPTH, self::JSON_DECODE_OPTIONS);
    }
}
