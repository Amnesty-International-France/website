<?php


if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

function duplicate_post($post_id, $suffixe, ?string $post_date = null): void
{
    $post = get_post($post_id);
    $newPost = [
        'post_title' => $post->post_title,
        'post_author' => $post->post_author,
        'post_content' => $post->post_content,
        'post_excerpt' => $post->post_excerpt,
        'post_name' => $post->post_name.$suffixe,
        'post_status' => $post_date ? 'future' : 'draft',
        'post_type' => $post->post_type,
    ];
    if ($post_date) {
        $newPost['post_date'] = $post_date;
        $newPost['post_date_gmt'] = get_gmt_from_date($post_date);
    }



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
    $suffixe = $assoc_args['suffixe'] ?? null;
    $publish_date = $assoc_args['publish_date'] ?? null;

    if (!isset($suffixe)) {
        WP_CLI::error("L'option --suffixe est obligatoire ! Exemple : wp duplicate-countries --suffixe=-2025");
    }

    $suffixe = $assoc_args['suffixe'];
    WP_CLI::line('Duplication des pages pays');

    $duplicatedCountries = 0;
    $page = 1;
    $batch_size = 10;


    do {
        $query = [
            'post_type' => 'fiche_pays',
            'post_status' => 'publish',
            'posts_per_page' => $batch_size,
            'paged' => $page,
        ];

        if ($publish_date) {
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $publish_date)) {
                WP_CLI::error('Format de date invalide. Exemple : "2026-04-15 12:00:00"');
            }

            $query = [
                ...$query,
                'post_date' => $publish_date,
            ];
        }

        $query_wp =  new WP_Query($query);

        if (! $query_wp->have_posts()) {
            break; // On sort si la page est vide
        }

        foreach ($query_wp->posts as $post) {
            duplicate_post($post->ID, $suffixe, $publish_date ?? null);
            $duplicatedCountries++;
        }

        wp_reset_postdata();

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        $page++;
    } while ($query_wp->have_posts());

    WP_CLI::success("$duplicatedCountries posts ont été copiés");

};

WP_CLI::add_command('duplicate-countries', $duplicate_countries);
