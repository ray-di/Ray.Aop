<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Ray\Aop\Exception\NotWritableException;

final class Code
{
    /**
     * @var string
     */
    public $code;

    /**
     * @param array<Node> $stmt
     */
    public function __construct(array $stmt)
    {
        $this->code = (new Standard(['shortArraySyntax' => true]))->prettyPrintFile($stmt) . PHP_EOL;
    }

    public function save(string $classDir, string $aopClassName) : string
    {
        class_exists($aopClassName);
        $flatName = str_replace('\\', '_', $aopClassName);
        $filename = sprintf('%s/%s.php', $classDir, $flatName);
        $tmpFile = tempnam(dirname($filename), 'swap');
        if (is_string($tmpFile) && file_put_contents($tmpFile, $this->code) && rename($tmpFile, $filename)) {
            return $filename;
        }
        @unlink((string) $tmpFile);

        throw new NotWritableException(sprintf('swap: %s, file: %s', $tmpFile, $filename));
    }
}
