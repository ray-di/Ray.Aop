<?php

declare(strict_types=1);

namespace Ray\Aop;

class AopCodeGenNewCode
{
    /** @var string */
    private $tmp = '';

    /** @var string */
    public $code = '';

    /** @var bool  */
    private $ignore = false;

    /** @return void */
    public function add(string $text)
    {
        if ($this->ignore) {
            return;
        }

        $this->tmp .= $text;
    }

    /** @return void */
    public function clear()
    {
        $this->tmp = '';
    }

    /** @return void */
    public function commit()
    {
        if ($this->ignore) {
            return;
        }

        $this->code .= $this->tmp;
        $this->tmp = '';
    }

    /** @return void */
    public function ignore(bool $ignore)
    {
        $this->ignore = $ignore;
    }
}
