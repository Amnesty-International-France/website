<?php

function get_or_create_page($post_type, $slug, $title, $params = [], $content = ''): WP_Post|int|false
{
    $exists = get_page_by_path($slug, OBJECT, $post_type);
    if ($exists) {
        return $exists;
    } else {
        if (PrismicMigrationCli::$dryrun) {
            return 0;
        }
        $id = wp_insert_post(array_merge($params, [
            'post_title' => $title,
            'post_name' => $slug,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_content' => $content,
        ]));
        if (is_wp_error($id)) {
            return false;
        }
        return $id;
    }
}
