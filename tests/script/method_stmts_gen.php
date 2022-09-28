<?php

declare(strict_types=1);

use PhpParser\BuilderFactory;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$factory = new BuilderFactory();
$voidCall = $factory->methodCall(
    $factory->var('this'),
    '_intercept',
    [$factory->funcCall('func_get_args'), $factory->constFetch('__FUNCTION__')]
);

$returnCall = new PhpParser\Node\Stmt\Return_(
    $factory->methodCall(
        $factory->var('this'),
        '_intercept',
        [$factory->funcCall('func_get_args'), $factory->constFetch('__FUNCTION__')]
    )
);

$nodeVoid = $factory->namespace('a')->addStmt($voidCall)->getNode();
$nodeReturn = $factory->namespace('a')->addStmt($returnCall)->getNode();

$prettyPrinter = new PhpParser\PrettyPrinter\Standard();

echo $prettyPrinter->prettyPrintFile([$nodeVoid]);
echo $prettyPrinter->prettyPrintFile([$nodeReturn]);
