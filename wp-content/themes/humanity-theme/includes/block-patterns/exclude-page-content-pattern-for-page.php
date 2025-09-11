<?php

declare(strict_types=1);

/**
 * Masque un pattern spécifique sur un modèle de page donné.
 *
 * @param string $block_content Le contenu HTML du bloc.
 * @param array $block Les informations et attributs du bloc.
 * @return string              Le contenu du bloc, modifié ou non.
 */
function exclude_page_content_pattern_for_page($block_content, $block)
{
    if (!is_page()) {
        return $block_content;
    }

    $current_template_slug = get_page_template_slug(get_the_ID());

    $excluded_in_page = ['page-don', 'page-fondation'];

    if (
        isset($block['blockName']) &&
        'amnesty-core/related-posts' === $block['blockName'] &&
        \in_array($current_template_slug, $excluded_in_page, true)
    ) {
        return '';
    }

    return $block_content;
}

add_filter('render_block', 'exclude_page_content_pattern_for_page', 10, 2);
