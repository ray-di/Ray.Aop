<?php

declare(strict_types=1);

namespace Ray\Aop;

use function dirname;

$loader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
$loader->addPsr4('Ray\Aop\\', dirname(__DIR__) . '/Fake');

$pointcut = new Pointcut(
    (new Matcher())->any(),
    (new Matcher())->any(),
    [new FakeInterceptor()]
);
$bind = (new Bind());
$bind->bind(FakeWeaverScript::class, [$pointcut]);
$weaver = new Weaver($bind, dirname(__DIR__) . '/tmp');
$weaver->weave(FakeWeaverScript::class);
exit;
