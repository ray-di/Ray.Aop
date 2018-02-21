<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\ParserFactory as PhpParserFactory;

class ParserFactory
{
    public function newInstance()
    {
        return (new PhpParserFactory)->create(PhpParserFactory::PREFER_PHP7);
    }
}
