<?php

declare(strict_types=1);

namespace Ray\Aop;

use Ray\Aop\Exception\NotWritableException;

final class Code
{
    /**
     * @var string
     */
    public $code = '';

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
