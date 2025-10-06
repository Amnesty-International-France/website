<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/wp-content/themes/humanity-theme',
        __DIR__.'/wp-content/plugins/aif-donor-space',
		__DIR__.'/wp-content/plugins/aif-rss-importer',
        __DIR__.'/wp-content/plugins/interactive-map',
        __DIR__.'/wp-content/plugins/prismic-migration',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'indentation_type' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder);
