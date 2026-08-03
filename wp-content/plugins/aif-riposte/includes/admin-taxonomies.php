<?php

/**
 * Riposte victory admin taxonomies.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Limit selected terms to the last submitted term.
 *
 * @param int                $object_id            Post ID.
 * @param string|int|array   $terms                Submitted terms.
 * @param array<int>         $term_taxonomy_ids    Assigned term taxonomy IDs.
 * @param string             $taxonomy             Taxonomy name.
 * @param bool               $append               Whether terms are appended.
 * @param array<int>         $old_term_taxonomy_ids Previously assigned term taxonomy IDs.
 *
 * @return void
 */
function aif_riposte_limit_single_taxonomy_term(
    int $object_id,
    $terms,
    array $term_taxonomy_ids,
    string $taxonomy,
    bool $append,
    array $old_term_taxonomy_ids
): void {
    unset($terms, $append, $old_term_taxonomy_ids);

    static $is_updating = false;

    if ($is_updating) {
        return;
    }

    if ('riposte_victory' !== get_post_type($object_id)) {
        return;
    }

    $limited_taxonomies = [
        'riposte_theme',
        'riposte_tag',
        'location',
    ];

    if (! in_array($taxonomy, $limited_taxonomies, true)) {
        return;
    }

    if (count($term_taxonomy_ids) <= 1) {
        return;
    }

    if (! current_user_can('edit_post', $object_id)) {
        return;
    }

    $last_term_taxonomy_id = (int) end($term_taxonomy_ids);

    $term = get_term_by(
        'term_taxonomy_id',
        $last_term_taxonomy_id,
        $taxonomy
    );

    if (! $term instanceof WP_Term) {
        return;
    }

    $is_updating = true;

    wp_set_object_terms(
        $object_id,
        [ $term->term_id ],
        $taxonomy,
        false
    );

    $is_updating = false;
}

add_action(
    'set_object_terms',
    'aif_riposte_limit_single_taxonomy_term',
    20,
    6
);
