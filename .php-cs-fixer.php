<?php

declare(strict_types=1);

/**
 * PHP-CS-Fixer Configuration for KaririCode\Parser.
 *
 * Ensures PSR-12 compliance while preserving premium documentation.
 *
 * @package KaririCode\Parser
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   1.0.0
 */

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->notPath('vendor');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // ====================================================================
        // BASE RULE SETS
        // ====================================================================
        '@PSR12' => true,
        '@PHP8x3Migration' => true,  // Usar PHP83 em vez de PHP84 (mais estável)

        // ====================================================================
        // CRITICAL: COMMENT PRESERVATION RULES
        // ====================================================================
        'no_empty_phpdoc' => false,
        'no_superfluous_phpdoc_tags' => false,
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_no_access' => false,
        'phpdoc_no_package' => false,
        'phpdoc_summary' => false,
        'phpdoc_order' => false,
        'phpdoc_separation' => false,
        'phpdoc_tag_type' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_add_missing_param_annotation' => false,
        'no_blank_lines_after_phpdoc' => false,
        'phpdoc_align' => false,
        'phpdoc_indent' => false,
        'phpdoc_trim' => false,
        'no_trailing_whitespace_in_comment' => false,
        'single_line_comment_style' => false,
        'multiline_comment_opening_closing' => false,
        'comment_to_phpdoc' => false,
        'no_empty_comment' => false,

        // ====================================================================
        // ARRAYS
        // ====================================================================
        'array_syntax' => ['syntax' => 'short'],
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        'trim_array_spaces' => true,
        'normalize_index_brace' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        // ====================================================================
        // BLANK LINES
        // ====================================================================
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        // 'blank_lines_before_namespace' => [
        //     'min_line_breaks' => 1,
        //     'max_line_breaks' => 1,
        // ],
        'no_blank_lines_after_class_opening' => true,
        'no_extra_blank_lines' => [
            'tokens' => ['extra', 'throw', 'use'],
        ],

        // ====================================================================
        // CASTS
        // ====================================================================
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'short_scalar_cast' => true,
        'no_unset_cast' => true,

        // ====================================================================
        // CLASSES
        // ====================================================================
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'single_class_element_per_statement' => true,
        'modifier_keywords' => true,  // Substitui visibility_required
        'no_null_property_initialization' => true,
        'self_accessor' => true,

        // ====================================================================
        // CONTROL STRUCTURES
        // ====================================================================
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'yoda_style' => false,
        'elseif' => true,

        // ====================================================================
        // FUNCTIONS
        // ====================================================================
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'type_declaration_spaces' => true,  // Substitui function_typehint_space
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'void_return' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,  // Substitui native_function_type_declaration_casing

        // ====================================================================
        // IMPORTS
        // ====================================================================
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'no_leading_import_slash' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'fully_qualified_strict_types' => true,

        // ====================================================================
        // NAMESPACES
        // ====================================================================
        'no_leading_namespace_whitespace' => false,

        // ====================================================================
        // OPERATORS
        // ====================================================================
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'concat_space' => ['spacing' => 'one'],
        'unary_operator_spaces' => true,
        'ternary_operator_spaces' => true,
        'new_with_parentheses' => true,
        'object_operator_without_whitespace' => true,
        'standardize_not_equals' => true,
        'ternary_to_null_coalescing' => true,

        // ====================================================================
        // PHP TAGS
        // ====================================================================
        'full_opening_tag' => true,
        'no_closing_tag' => true,

        // ====================================================================
        // PHPDOC (SAFE RULES)
        // ====================================================================
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,

        // ====================================================================
        // SEMICOLONS
        // ====================================================================
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => [
            'remove_in_empty_for_expressions' => true,
        ],

        // ====================================================================
        // STRINGS
        // ====================================================================
        'single_quote' => true,
        'simple_to_complex_string_variable' => true,

        // ====================================================================
        // WHITESPACE
        // ====================================================================
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'statement_indentation' => true,

        // ====================================================================
        // STRICT TYPING & SAFETY
        // ====================================================================
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        // ====================================================================
        // PHP 8.4+ FEATURES
        // ====================================================================
        'modernize_types_casting' => true,
        'no_alias_functions' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],

        // ====================================================================
        // CODE CLEANUP
        // ====================================================================
        'no_unreachable_default_argument_value' => true,
        'no_useless_return' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setIndent('    ')
    ->setLineEnding("\n");
