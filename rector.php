<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Ssch\TYPO3Rector\CodeQuality\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Rector\PHPUnit\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\TYPO314\v0\MigrateGeneralUtilityCreateVersionNumberedFilenameRector;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/Classes', __DIR__ . '/Configuration', __DIR__ . '/Tests'])
    ->withPhpSets(true)
    ->withAttributesSets(doctrine: true, phpunit: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withImportNames(removeUnusedImports: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        Typo3LevelSetList::UP_TO_TYPO3_14,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    # To have a better analysis from PHPStan, we teach it here some more things
    ->withPHPStanConfigs([Typo3Option::PHPSTAN_FOR_RECTOR_PATH])
    ->withRules([ConvertImplicitVariablesToExplicitGlobalsRector::class, DeclareStrictTypesRector::class])
    # If you use withImportNames(), you should consider excluding some TYPO3 files.
    ->withSkip([
        // @see https://github.com/sabbelasichon/typo3-rector/issues/2536
        __DIR__ . '/Resources/*',
        __DIR__ . '/vendor/*',
        __DIR__ . '/Build/*',
        __DIR__ . '/public/*',
        __DIR__ . '/.github/*',
        __DIR__ . '/.Build/*',
        // Rector's mechanical migration assumes an EXT:/PKG:/App resource identifier, but here the
        // value is already a FAL-driver-resolved public URL - applying it would produce broken CDN URLs.
        // Safe to defer: the call is only deprecated in v14, removed in v15.
        MigrateGeneralUtilityCreateVersionNumberedFilenameRector::class => [
            __DIR__ . '/Classes/EventListener/AddCdnToResource.php',
        ],
        // This mock is shared across tests with different expected call counts (some short-circuit
        // before the "site" attribute is even read) - a fixed exactly(2) expectation breaks half of them.
        DecorateWillReturnMapWithExpectsMockRector::class => [
            __DIR__ . '/Tests/Unit/SystemResource/CdnSystemResourcePublisherTest.php',
        ],
    ]);
