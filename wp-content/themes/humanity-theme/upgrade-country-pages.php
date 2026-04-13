<?php

function depublish_post($post)
{
    $currentStatus = $post->post_status;
    if ($currentStatus !== 'publish') {
        return false;
    }

    $result = wp_update_post([
        'ID' => $post->ID,
        'post_status' => 'draft',
    ], true);

    return !is_wp_error($result);
}

function update_permalink($post, $newSuffix, $oldSuffix, $removeSuffix = false)
{
    $currentPermalink = $post->post_name;

    $newPermalink = $removeSuffix ? str_replace($oldSuffix, '', $currentPermalink) : $currentPermalink . $newSuffix;

    $result = wp_update_post([
        'ID' => $post->ID,
        'post_name' => $newPermalink,
    ], true);

    return !is_wp_error($result);
}

function publish_post($post)
{
    $currentStatus = $post->post_status;
    if ($currentStatus !== 'draft') {
        return false;
    }

    $result = wp_update_post([
        'ID' => $post->ID,
        'post_status' => 'publish',
    ], true);

    return !is_wp_error($result);
}

$update_countries = function ($args, $assoc_args) {
    if (!isset($assoc_args['value-to-add'])) {
        WP_CLI::error("L'option --value-to-add est obligatoire ! Exemple : wp update-countries --value-to-add=-2024");
    }
    if (!isset($assoc_args['value-to-remove'])) {
        WP_CLI::error("L'option --value-to-remove est obligatoire ! Exemple : wp update-countries --value-to-remove=-2025");
    }
    $adding = $assoc_args['value-to-add'];
    $removing = $assoc_args['value-to-remove'];

    WP_CLI::line('Mise à jour des pages pays');

    $depublishedCount = 0;
    $publishedCount = 0;
    $batchSize = 20;
    $page = 1;

    do {
        $query = new WP_Query([
            'post_type' => 'fiche_pays',
            'post_status' => ['publish', 'draft'],
            'orderby' => 'title',
            'order' => 'ASC',
            'posts_per_page' => $batchSize,
            'paged' => $page,
        ]);

        if (! $query->have_posts()) {
            break; // On sort si la page est vide
        }

        $oldPages = array_filter(
            $query->posts,
            static fn ($post) => !str_ends_with($post->post_name, $removing)
        );
        $newPages = array_filter(
            $query->posts,
            static fn ($post) => str_ends_with($post->post_name, $removing)
        );

        foreach ($oldPages as $post) {
            if (depublish_post($post) && update_permalink($post, $adding, $removing, false)) {
                $depublishedCount++;
            }
        }
        wp_reset_postdata();

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        foreach ($newPages as $post) {
            if (update_permalink($post, $adding, $removing, true) && publish_post($post)) {
                $publishedCount++;
            }
        }
        wp_reset_postdata();

        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        $page++;
    } while ($query->have_posts());

    WP_CLI::success("$depublishedCount posts ont été dépubliés");
    WP_CLI::success("$publishedCount posts ont été publiés");
};

WP_CLI::add_command('update-countries', $update_countries);
