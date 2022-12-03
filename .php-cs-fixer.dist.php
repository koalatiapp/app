<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('node_modules')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        '@PHP81Migration' => true,
        'yoda_style' => ['equal' => false, 'identical' => false],
        'increment_style' => ['style' => 'post'],
        'indentation_type' => true,
        'line_ending' => true,
        'statement_indentation' => true,
		'single_quote' => false,
    ])
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setFinder($finder);
