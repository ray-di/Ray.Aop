<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('Template.php')
    ->exclude('docs')
    ->exclude('vendor')
    ->exclude('.idea')
    ->exclude('cs')
    ->exclude('tmp')
    ->exclude('build')
    ->in(__DIR__ . '/src')
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
;
