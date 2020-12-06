<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Cache\PhpFileCache;

class CachedCompilerTest extends CompilerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $tmpDir = __DIR__ . '/tmp';
        $this->compiler = new CachedCompiler($tmpDir, new PhpFileCache($tmpDir));
    }
}
