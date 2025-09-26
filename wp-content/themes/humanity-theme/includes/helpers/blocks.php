<?php

declare(strict_types=1);

if (! function_exists('amnesty_find_first_block_of_type')) {
    /**
     * Retrieve header block from an array of parsed blocks
     *
     * @package Amnesty\Blocks
     *
     * @param array<int,array<string,mixed>> $blocks     the parsed blocks
     * @param string                         $block_name the block to find
     *
     * @return array<string,mixed>
     */
    function amnesty_find_first_block_of_type(array $blocks = [], string $block_name = ''): array
    {
        foreach ($blocks as $block) {
            if ($block['blockName'] === $block_name) {
                return $block;
            }

            if (empty($block['innerBlocks'])) {
                continue;
            }

            return amnesty_find_first_block_of_type($block['innerBlocks'], $block_name);
        }

        return [];
    }
}

if (! function_exists('amnesty_string_to_paragraphs')) {
    /**
     * Convert a string of text to paragraph block markup
     *
     * @param string $content the content to transform
     *
     * @return string
     */
    function amnesty_string_to_paragraphs(string $content): string
    {
        $content = wpautop($content);
        $content = explode('</p>', $content);
        $content = array_filter(array_map('trim', $content));
        $output  = '';

        foreach ($content as $paragraph) {
            $output .= PHP_EOL;
            $output .= '<!-- wp:paragraph -->';
            $output .= PHP_EOL;
            $output .= $paragraph . '</p>';
            $output .= PHP_EOL;
            $output .= '<!-- /wp:paragraph -->';
            $output .= PHP_EOL;
        }

        return trim($output);
    }
}

add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {

    $special_blocks = [
        'amnesty-core/actions-homepage',
        'amnesty-core/agenda-homepage',
        'amnesty-core/articles-homepage',
        'amnesty-core/hero-homepage',
        'amnesty-core/mission-homepage',
    ];

    $homepage_id = (int) get_option('page_on_front');

    $mon_espace_page = get_page_by_path('mon-espace');
    $mon_espace_id = $mon_espace_page ? (int) $mon_espace_page->ID : 0;

    $current_post_id = 0;
    if (isset($editor_context->post) && isset($editor_context->post->ID)) {
        $current_post_id = (int) $editor_context->post->ID;
    }

    if ($current_post_id === 0) {
        return $allowed_blocks;
    }

    if ($current_post_id === $homepage_id || ($mon_espace_id !== 0 && $current_post_id === $mon_espace_id)) {

        if ($allowed_blocks === true) {
            return true;
        }
        if (is_array($allowed_blocks)) {
            foreach ($special_blocks as $block) {
                if (!in_array($block, $allowed_blocks, true)) {
                    $allowed_blocks[] = $block;
                }
            }
        }
        return $allowed_blocks;
    }

    if ($allowed_blocks === true) {
        $all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $allowed_blocks = array_keys($all_blocks);
    }

    if (is_array($allowed_blocks)) {
        $allowed_blocks = array_filter($allowed_blocks, function ($block_name) use ($special_blocks) {
            return !in_array($block_name, $special_blocks, true);
        });
        $allowed_blocks = array_values($allowed_blocks);
    }

    return $allowed_blocks;

}, 9999, 2);
