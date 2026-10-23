<?php

declare(strict_types=1);

/**
 * Keep unknown/dynamic blocks; only discard empty editorial containers and text.
 *
 * @param array<string, mixed> $block Parsed Gutenberg block.
 */
function amnesty_clh_presentation_has_content(array $block): bool
{
    if (!in_array($block['blockName'] ?? '', ['core/column', 'core/columns', 'core/group', 'core/paragraph', 'core/heading', 'core/image'], true)) {
        return true;
    }

    foreach ($block['innerBlocks'] ?? [] as $child) {
        if (amnesty_clh_presentation_has_content($child)) {
            return true;
        }
    }

    $html = $block['innerHTML'] ?? '';
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace("\xc2\xa0", ' ', $text);

    return trim($text) !== '' || preg_match('/<img\b[^>]*\bsrc\s*=\s*[\x22\x27][^\x22\x27]+[\x22\x27]/i', $html) === 1;
}

/**
 * @param array<string, mixed> $block Parsed Gutenberg block.
 */
function amnesty_clh_is_situations_row(array $block): bool
{
    return ($block['blockName'] ?? '') === 'core/columns'
        && in_array('clh-situations', preg_split('/\s+/', $block['attrs']['className'] ?? '') ?: [], true);
}

/**
 * Remove empty situations columns at render time, leaving the editor content intact.
 *
 * @param array<string, mixed> $block Parsed Gutenberg block.
 * @return array<string, mixed>
 */
function amnesty_clh_prepare_presentation(array $block): array
{
    if (!amnesty_clh_is_situations_row($block)) {
        return $block;
    }

    $columns = $block['innerBlocks'] ?? [];
    $keep = array_map('amnesty_clh_presentation_has_content', $columns);
    $index = 0;
    $block['innerContent'] = array_values(array_filter(
        $block['innerContent'] ?? [],
        static function ($chunk) use ($keep, &$index): bool {
            return $chunk !== null || ($keep[$index++] ?? false);
        },
    ));
    $block['innerBlocks'] = array_values(array_filter($columns, 'amnesty_clh_presentation_has_content'));

    if (!$block['innerBlocks']) {
        $block['innerContent'] = [];
        $block['innerHTML'] = '';
    }

    return $block;
}

/**
 * Drop the HTML of a situations row whose columns were all removed.
 *
 * Nested blocks ignore an emptied innerContent (WP_Block::refresh_parsed_block_dependents()
 * only copies non-empty values), so the old columns would render without this.
 *
 * @param array<string, mixed> $block Parsed Gutenberg block, after render_block_data.
 */
function amnesty_clh_hide_empty_presentation(string $content, array $block): string
{
    return amnesty_clh_is_situations_row($block) && !($block['innerBlocks'] ?? []) ? '' : $content;
}

add_filter('render_block_data', 'amnesty_clh_prepare_presentation');
add_filter('render_block', 'amnesty_clh_hide_empty_presentation', 10, 2);
