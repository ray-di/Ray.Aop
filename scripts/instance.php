<?php

namespace Ray\Aop;

use PHPParser_PrettyPrinter_Default;
use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_BuilderFactory;

return new Compiler(
    sys_get_temp_dir(),
    new PHPParser_PrettyPrinter_Default,
    new PHPParser_Parser(new PHPParser_Lexer),
    new PHPParser_BuilderFactory
);
