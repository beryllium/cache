<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php81: true)
    ->withTypeCoverageLevel(70)
    ->withDeadCodeLevel(70)
    ->withCodeQualityLevel(70);
