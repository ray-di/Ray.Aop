<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/demo',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        // Skip traits with parent::class usage - Rector cannot resolve parent context in traits
        __DIR__ . '/src/InterceptTrait.php',
        __DIR__ . '/src/ReadOnlyInterceptTrait.php',
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(1)
    ->withDeadCodeLevel(1)
    ->withCodeQualityLevel(1);
