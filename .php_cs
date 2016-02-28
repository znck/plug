<?php

use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;
use Symfony\CS\Finder\DefaultFinder;

$fixers = [
    'no_blank_lines_before_namespace',
];

return Config::create()
    ->finder(DefaultFinder::create()->in(__DIR__))
    ->fixers($fixers)
    ->level(FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true);
