<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/src', __DIR__.'/tests'])
    ->notPath(['.php-cs-fixer.dist.php']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'blank_line_between_import_groups' => false,
        'no_trailing_comma_in_singleline' => false,
        'no_multiline_whitespace_around_double_arrow' => false,
        'strict_param' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_hidden_params' => true,
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
        'phpdoc_to_comment' => false,
        'phpdoc_align' => false,
        'native_function_invocation' => ['exclude' => ['sprintf']],
        'single_line_throw' => false,
        'declare_strict_types' => true,
    ]);
