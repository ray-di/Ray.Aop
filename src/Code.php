<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Ray\Aop\Exception\NotWritableException;

use function dirname;
use function file_put_contents;
use function is_string;
use function rename;
use function sprintf;
use function tempnam;
use function unlink;

use const PHP_EOL;

final class Code
{
    /** @var string */
    public $code;

    /** @param array<Node> $stmt */
    public function __construct(array $stmt)
    {
        $this->code = (new Standard(['shortArraySyntax' => true]))->prettyPrintFile($stmt) . PHP_EOL;
    }

    public function save(string $filename): string
    {
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $this->code) && rename($tmpFile, $filename)) {
            return $filename;
        }

        // @codeCoverageIgnoreStart
        @unlink((string) $tmpFile);

        throw new NotWritableException(sprintf('swap: %s, file: %s', $tmpFile, $filename));

        // @codeCoverageIgnoreEnd
    }
}
