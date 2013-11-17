<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$code = '<?php
 // some code
class A
{
    /**
     * @Konichiwa
     */
    public function hello()
    {
    }
}
';

$parser = new PHPParser_Parser(new PHPParser_Lexer);

try {
    $stmts = $parser->parse($code);
    $nodeDumper = new PHPParser_NodeDumper;
    echo $nodeDumper->dump($stmts);
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}