<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Exception\NotWritableException;

use function file_get_contents;
use function fileperms;
use function glob;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function umask;
use function uniqid;
use function unlink;

class FilePutContentsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ray-aop-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach ($this->entries() as $path) {
            is_dir($path) ? rmdir($path) : unlink($path);
        }

        rmdir($this->dir);
    }

    public function testWritesContentWithoutLeavingSwapFile(): void
    {
        $file = $this->dir . '/a.php';
        (new FilePutContents())($file, '<?php return 1;');

        $this->assertSame('<?php return 1;', file_get_contents($file));
        $this->assertSame([$file], $this->entries());
    }

    public function testReplacesExistingFile(): void
    {
        $file = $this->dir . '/a.php';
        (new FilePutContents())($file, 'first');
        (new FilePutContents())($file, 'second');

        $this->assertSame('second', file_get_contents($file));
        $this->assertSame([$file], $this->entries());
    }

    public function testFollowsUmask(): void
    {
        $file = $this->dir . '/a.php';
        (new FilePutContents())($file, 'x');

        $this->assertSame(0666 & ~umask(), fileperms($file) & 0777);
    }

    public function testThrowsAndRemovesSwapFileWhenRenameFails(): void
    {
        $file = $this->dir . '/taken';
        mkdir($file);

        try {
            (new FilePutContents())($file, 'x');
            $this->fail();
        } catch (NotWritableException $e) {
            $this->assertSame($file, $e->getMessage());
        }

        $this->assertSame([$file], $this->entries());
    }

    /** @return list<string> */
    private function entries(): array
    {
        return glob($this->dir . '/*') ?: [];
    }
}
