<?php

namespace Ray\Aop;

use PHPParser_PrettyPrinter_Default;

return new Compiler(
    sys_get_temp_dir(),
    new PHPParser_PrettyPrinter_Default
);
