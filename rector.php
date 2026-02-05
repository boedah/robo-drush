<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uses PHP version of composer.json:
    ->withPhpSets()
    // uses PHPUnit version of composer.json:
    ->withComposerBased(phpunit: true)
    ->withPreparedSets(phpunitCodeQuality: true)
    ->withRules([
        // rector
        AddVoidReturnTypeWhereNoReturnRector::class,
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
    ]);
