<?php

declare(strict_types=1);

namespace Ray\Aop;

use PhpParser\Parser;
use PhpParser\ParserFactory as PhpParserFactory;

class ParserFactory
{
    public function newInstance() : Parser
    {
        return (new PhpParserFactory)->create(PhpParserFactory::PREFER_PHP7);
    }
}
