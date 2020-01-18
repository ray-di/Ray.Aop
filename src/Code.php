<?php

declare(strict_types=1);

namespace Ray\Aop;

final class Code
{
    /**
     * @var string
     */
    public $code = '';

    public function save(string $classDir, string $aopClassName) : string
    {
        $flatName = str_replace('\\', '_', $aopClassName);
        $file = sprintf('%s/%s.php', $classDir, $flatName);
        file_put_contents($file, $this->code . PHP_EOL);

        return $file;
    }
}
