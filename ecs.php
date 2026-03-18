<?php

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NullableTypeDeclarationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/src/Migrations'
    ])
    ->withRules([
        NullableTypeDeclarationFixer::class,
        NoUnusedImportsFixer::class,
        NullableTypeDeclarationForDefaultNullValueFixer::class
    ])
    ->withConfiguredRule(
        ArraySyntaxFixer::class,
        [
            'syntax' => 'short',
        ]
    )
    ->withConfiguredRule(MethodArgumentSpaceFixer::class, [
        'on_multiline' => 'ensure_fully_multiline',
    ])
    ->withConfiguredRule(
        NativeFunctionInvocationFixer::class,
        [
            'scope' => 'namespaced',
            'include' => ['@compiler_optimized']
        ]
    );
