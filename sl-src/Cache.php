<?php

    declare(strict_types=1);

    namespace Ray\ServiceLocator;

    use Ray\ServiceLocator\Exception\DirectoryNotWritableException;

    use function file_exists;
    use function file_put_contents;
    use function hash;
    use function is_dir;
    use function is_writable;
    use function mkdir;
    use function pathinfo;
    use function rename;
    use function serialize;
    use function substr;
    use function tempnam;
    use function unlink;
    use function unserialize;

    use const DIRECTORY_SEPARATOR;
    use const PATHINFO_DIRNAME;

/**
 * Minimal cache
 */
final class Cache
{
    /** @var string  */
    private $tmpDir;

    public function __construct(string $tmpDir)
    {
        if (! is_writable($tmpDir)) {
            throw new DirectoryNotWritableException($tmpDir);
        }

        $this->tmpDir = $tmpDir;
    }

    /**
     * @psalm-param callable():array<object> $callback
     *
     * @return array<object>
     *
     * @psalm-suppress MixedInferredReturnType
     */
    public function get(string $key, callable $callback): array
    {
        $filename = $this->getFilename($key);
        if (! file_exists($filename)) {
            $value = $callback();
            $this->writeFile($this->getFilename($key), serialize($value));

            return $value;
        }

        /** @psalm-suppress MixedAssignment, MixedArgument, MixedReturnStatement */
        return unserialize(require $filename); // @phpstan-ignore-line
    }

    private function getFilename(string $id): string
    {
        $hash = hash('crc32', $id);

        $dir = $this->tmpDir
        . DIRECTORY_SEPARATOR
        . substr($hash, 0, 2);
        if (! is_dir($dir) && ! @mkdir($dir) && ! is_dir($dir) && ! is_writable($dir)) {
            // @codeCoverageIgnoreStart
            throw new DirectoryNotWritableException($dir);
            // @codeCoverageIgnoreEnd
        }

        return $dir
        . DIRECTORY_SEPARATOR
        . $hash
        . '.php';
    }

    private function writeFile(string $filename, string $value): void
    {
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);
        if (! is_writable($filepath)) {
            // @codeCoverageIgnoreStart
            throw new DirectoryNotWritableException($filepath);
            // @codeCoverageIgnoreEnd
        }

        $tmpFile = (string) tempnam($filepath, 'swap');
        $valueWithSingileQuote = "'{$value}'";

        $content = '<?php return ' . $valueWithSingileQuote . ';';
        if (file_put_contents($tmpFile, $content) !== false) {
            if (@rename($tmpFile, $filename)) {
                return;
            }

            // @codeCoverageIgnoreStart
            @unlink($tmpFile);
        }

        throw new DirectoryNotWritableException($filepath);
        // @codeCoverageIgnoreEnd
    }
}
