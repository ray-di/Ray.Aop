<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\ServiceLocator\Cache;
use stdClass;

use function mkdir;
use function uniqid;

/** @psalm-suppress DeprecatedClass */
class CacheTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function valueProvider(): array
    {
        return [
            'plain' => ['plain'],
            'single quote' => ["it's"],
            'php expression' => ["'.strtoupper('x').'"],
            'backslashes' => ['a\\\\b'],
            'NUL and newline' => ["a\0b\nc"],
        ];
    }

    /** @dataProvider valueProvider */
    public function testCachedValueIsReadBackUnchanged(string $value): void
    {
        $dir = __DIR__ . '/tmp/' . uniqid('cache');
        mkdir($dir);
        $object = new stdClass();
        $object->value = $value;
        $cache = new Cache($dir);
        $cache->get('key', static fn (): array => [$object]);

        $cached = $cache->get('key', static fn (): array => []);

        $this->assertEquals([$object], $cached);
    }
}
