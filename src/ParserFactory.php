<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as PHPParserFactory;

class ParserFactory
{
    public function newInstance()
    {
        if (class_exists('PhpParser\ParserFactory')) {
            return (new PHPParserFactory)->create(PHPParserFactory::PREFER_PHP7);
        }

        return new Parser(new Lexer());  // @codeCoverageIgnore
    }
}
