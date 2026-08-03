<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    // Style is owned by Pint — keep Rector off coding-style rules so the two do not fight.
    ->withPhpSets()
    ->withTypeCoverageLevel(53)
    ->withDeadCodeLevel(53)
    ->withCodeQualityLevel(53)
    ->withImportNames()
    ->withComposerBased(phpunit: true, laravel: true);
