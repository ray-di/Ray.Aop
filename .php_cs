<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('Template.php')
    ->in(__DIR__ . '/src')
;

return Symfony\CS\Config\Config::create()
    ->finder($finder)
;
