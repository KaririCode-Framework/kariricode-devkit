<?php

declare(strict_types=1);

/**
 * KaririCode DevKit - Rector Configuration
 *
 * Automated refactoring configuration to keep code modern and optimized.
 * Rector helps upgrade PHP code and apply best practices automatically.
 *
 * @see https://getrector.com/documentation
 */

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        // Skip test fixtures
        __DIR__ . '/tests/Fixtures',
    ])
    // PHP 8.3 features and best practices
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_83,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withRules([
        // PHP 8.0+
        InlineConstructorDefaultToPropertyRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        MixedTypeRector::class,

        // PHP 8.1+
        ReadOnlyPropertyRector::class,
        FinalizePublicClassConstantRector::class,
        FirstClassCallableRector::class,

        // PHP 8.2+
        ReadOnlyClassRector::class,

        // PHP 8.3+
        AddOverrideAttributeToOverriddenMethodsRector::class,

        // Code Quality
        FlipTypeControlToUseExclusiveTypeRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        RemoveUnusedPrivateMethodParameterRector::class,
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveConcatAutocastRector::class,
        RemoveAlwaysTrueIfConditionRector::class,

        // Coding Style
        StaticClosureRector::class,
        StaticArrowFunctionRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        UseClassKeywordForClassNameResolutionRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        PostIncDecToPreIncDecRector::class,

        // Early Return
        ChangeAndIfToEarlyReturnRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        ChangeOrIfContinueToMultiContinueRector::class,
        PreparedValueToEarlyReturnRector::class,

        // Type Declarations
        AddVoidReturnTypeWhereNoReturnRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        TypedPropertyFromStrictConstructorRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,

        // Naming
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        RenameParamToMatchTypeRector::class,

        // Strict Rules
        DisallowedEmptyRuleFixerRector::class,

        // PHPUnit
        PreferPHPUnitThisCallRector::class,
    ])
    ->withSkipRules([
        // Skip rules that conflict with our code style
        EncapsedStringsToSprintfRector::class,
        NewlineAfterStatementRector::class,
    ])
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true
    )
    ->withParallel()
    ->withCache(
        cacheDirectory: __DIR__ . '/.rector_cache'
    );
