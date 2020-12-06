<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Cache\PhpFileCache;

class CachedCompilerTest extends CompilerTest
{
    /** @var CachedCompiler */
    protected $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $classDir = __DIR__ . '/tmp';
        $cacheDir = __DIR__ . '/aop_cache';
        $this->compiler = new CachedCompiler($classDir, new PhpFileCache($cacheDir));
    }

    public function testCache(): void
    {
        $mock = $this->compiler->newInstance(FakeMockCached::class, [], $this->bind);
        $this->assertInstanceOf(FakeMockCached::class, $mock);
    }

    public function testFoo(): void
    {
        [$classDir] = require __DIR__ . '/define.php';
        deleteFiles($classDir);
        $mock = $this->compiler->newInstance(FakeMockCachedDeleted::class, [], $this->bind);
        $this->assertInstanceOf(FakeMockCachedDeleted::class, $mock);
    }
}
