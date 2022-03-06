<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'blank_line_before_statement' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'full_opening_tag' => false,
        'strict_comparison' => true,
        '@PSR1' => true,
        '@PHP80Migration:risky' => true,
    ])
    ->setFinder($finder)
    ;
