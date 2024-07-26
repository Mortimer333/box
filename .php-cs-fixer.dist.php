<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'concat_space' => false,
        'phpdoc_var_without_name' => false,
        'single_line_after_imports' => true,
        'class_definition' => false,
        'single_line_throw' => false,
    ])
    ->setFinder($finder)
;
