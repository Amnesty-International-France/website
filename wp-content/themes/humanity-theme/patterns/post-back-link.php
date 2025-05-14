<?php

/**
 * Title: Post Back Link
 * Description: Output back link to return to item's category archive
 * Slug: amnesty/post-back-link
 * Inserter: no
 */

$show_back_link = !amnesty_validate_boolish(amnesty_get_option('_display_category_label'));

if (!$show_back_link) {
    return;
}

$post_id = get_the_ID();
$post_type = get_post_type($post_id);

$label = '';
$link = '';

$main_category = amnesty_get_a_post_term($post_id);
$chip_style = match ($main_category->slug) {
    'actualites', 'dossiers' => 'bg-black',
    'chroniques' => 'bg-yellow',
    default => 'bg-black',
};

if ('landmark' === $post_type) {
    $repere_terms = wp_get_object_terms($post_id, 'landmark_category');

    if (!empty($repere_terms) && !is_wp_error($repere_terms)) {
        $main_category = $repere_terms[0];
        $label = $main_category->name;
        $link = '';

    } else {
        $post_type_object = get_post_type_object($post_type);
        $label = $post_type_object->labels->singular_name;
        $link = get_post_type_archive_link($post_type);
    }
} else {
    $taxonomies = get_object_taxonomies($post_type);
    $found_term = null;

    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy);
        if (!empty($terms) && !is_wp_error($terms)) {
            $found_term = $terms[0];
            break;
        }
    }

    if ($found_term) {
        $label = $found_term->name;
        $link = get_term_link($found_term);
    } else {
        $post_type_object = get_post_type_object($post_type);
        $label = $post_type_object->labels->singular_name;
        $link = get_post_type_archive_link($post_type);
    }
}

echo render_chip_category_block([
    'label' => $label,
    'link' => $link,
    'size' => 'large',
    'style' => $chip_style,
    'isLandmark' => ('landmark' === $post_type),
]);

