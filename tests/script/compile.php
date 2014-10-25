<?php

namespace Ray\Aop;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$loader->addPsr4('Ray\Aop\\', dirname(__DIR__) . '/Fake');

$tmpDir = dirname(__DIR__) . '/tmp';
$compiler = new Compiler($tmpDir);
$bind = new Bind;
$pointcut = new Pointcut(
    (new Matcher)->any(),
    (new Matcher)->any(),
    [new FakeInterceptor]
);
$bind->bind(FakeMock::class, [$pointcut]);
$class = $compiler->compile(FakeMock::class, $bind);

return $class;
