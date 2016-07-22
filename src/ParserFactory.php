<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as PhpParserFactory;

class ParserFactory
{
    public function newInstance()
    {
        if (class_exists('PhpParser\ParserFactory')) {
            return (new PhpParserFactory)->create(PhpParserFactory::PREFER_PHP7);
        }

        return new Parser(new Lexer());  // @codeCoverageIgnore
    }
}
