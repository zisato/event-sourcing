<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::PHP_81,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
    ]);

    $rectorConfig->skip([
        PrivatizeFinalClassMethodRector::class,
        UnSpreadOperatorRector::class,
        FinalizeClassesWithoutChildrenRector::class => [
            __DIR__ . '/src/Event/Event.php',
            __DIR__ . '/src/Aggregate/Repository/AggregateRootRepository.php',
            __DIR__ . '/src/Aggregate/Repository/AggregateRootRepositoryWithSnapshot.php',
        ]
    ]);
};