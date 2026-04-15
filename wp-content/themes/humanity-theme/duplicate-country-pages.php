<?php


if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

function duplicate_post($post_id, $suffixe)
{
    $post = get_post($post_id);
    $newPost = [
        'post_title' => $post->post_title,
        'post_author' => $post->post_author,
        'post_content' => $post->post_content,
        'post_excerpt' => $post->post_excerpt,
        'post_name' => $post->post_name.$suffixe,
        'post_status' => 'draft',
        'post_type' => $post->post_type,
    ];

    $newPostId = wp_insert_post($newPost, true);

    // copy original taxonomies and metadatas
    $taxonomies = get_object_taxonomies($post->post_type);
    if ($taxonomies) {
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'slugs']);
            wp_set_object_terms($newPostId, $post_terms, $taxonomy, false);
        }
    }
    $postMetas = get_post_meta($post_id);
    foreach ($postMetas as $metaKey => $metaValues) {
        foreach ($metaValues as $metaValue) {
            add_post_meta($newPostId, $metaKey, $metaValue);
        }
    }
}

$duplicate_countries = function ($args, $assoc_args) {

    if (!isset($assoc_args['suffixe'])) {
        WP_CLI::error("L'option --suffixe est obligatoire ! Exemple : wp duplicate-countries --suffixe=-2025");
    }

    $suffixe = $assoc_args['suffixe'];
    WP_CLI::line('Duplication des pages pays');

    $duplicatedCountries = 0;
    $page = 1;
    $batch_size = 10;


    do {
        $query = new WP_Query([
            'post_type' => 'fiche_pays',
            'post_status' => 'publish',
            'posts_per_page' => $batch_size,
            'paged' => $page,
        ]);

        if (! $query->have_posts()) {
            break; // On sort si la page est vide
        }

        foreach ($query->posts as $post) {
            duplicate_post($post->ID, $suffixe);
            $duplicatedCountries++;
        }

        wp_reset_postdata();

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        $page++;
    } while ($query->have_posts());

    WP_CLI::success("$duplicatedCountries posts ont été copiés");

};

WP_CLI::add_command('duplicate-countries', $duplicate_countries);
