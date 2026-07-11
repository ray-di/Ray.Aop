<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

use function class_exists;
use function file_exists;
use function glob;
use function mkdir;
use function rmdir;
use function str_replace;
use function uniqid;
use function unlink;

/**
 * compile() must (re)emit the weaved class file even when the class is already
 * declared in the process but the file has been removed — e.g. a compile
 * pipeline that cleans the script dir and recompiles. The file is needed by
 * runtime loaders that resolve the weaved class by name.
 */
final class WeavedFileReemissionTest extends TestCase
{
    public function testReemitsWeavedFileWhenClassDeclaredButFileDeleted(): void
    {
        $tmpDir = __DIR__ . '/tmp/weaved-reemission-' . uniqid('', true);
        @mkdir($tmpDir, 0777, true);

        try {
            $compiler = new Compiler($tmpDir);
            $bind = (new Bind())->bindInterceptors('returnSame', [new FakeDoubleInterceptor()]);

            // First weave: writes the file and declares the class in-process.
            $fqn = $compiler->compile(FakeMock::class, $bind);
            $file = $tmpDir . '/' . str_replace('\\', '_', $fqn) . '.php';
            $this->assertTrue(file_exists($file), 'first weave must write the file');
            $this->assertTrue(class_exists($fqn, false), 'first weave declares the class');

            // Simulate a compile-pipeline clean step: the file is removed while
            // the class stays declared in the process.
            unlink($file);
            $this->assertFalse(file_exists($file));

            // Re-weave: must re-emit the file even though the class is declared.
            $compiler->compile(FakeMock::class, $bind);
            $this->assertTrue(file_exists($file), 're-weave must re-emit the weaved file when it is missing');
        } finally {
            foreach (glob($tmpDir . '/*') ?: [] as $f) {
                @unlink($f);
            }

            @rmdir($tmpDir);
        }
    }
}
