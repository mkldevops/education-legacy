<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@PHPUnit84Migration:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PSR2' => true,
        'blank_line_before_statement' => true,
        'array_syntax' => ['syntax' => 'short'],
        'full_opening_tag' => false,
        'single_line_throw' => false,
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
    ;