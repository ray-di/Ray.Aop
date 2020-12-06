<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Doctrine\Common\Cache\PhpFileCache;
use Ray\Aop\Bind;
use Ray\Aop\CachedCompiler;
use Ray\Aop\FakeDoubleInterceptor;
use Ray\Aop\FakeMockCached;
use Ray\Aop\FakeMockCachedDeleted;
use Ray\Aop\FakeWeaved;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

[$classDir, $cacheDir] = require __DIR__ . '/define.php';
$compiler = new CachedCompiler($classDir, new PhpFileCache($cacheDir));
$matcher = new Matcher();
$pointcut = new Pointcut($matcher->any(), $matcher->startsWith('return'), [new FakeDoubleInterceptor()]);
$bind = (new Bind())->bind(FakeWeaved::class, [$pointcut]);
$fakeMockCached = $compiler->newInstance(FakeMockCached::class, [], $bind);
assert($fakeMockCached instanceof FakeMockCached);
$fakeMockCachedDeleted = $compiler->newInstance(FakeMockCachedDeleted::class, [], $bind);
assert($fakeMockCachedDeleted instanceof FakeMockCachedDeleted);
