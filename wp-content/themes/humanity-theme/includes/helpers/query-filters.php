<?php

function amnesty_filter_cpt_by_multiple_taxonomies(WP_Query $query)
{
    if (! $query->is_main_query()) {
        return;
    }

    if ($query->is_tax() && isset($_GET['qtype'])) {
        $post_types = explode(',', $_GET['qtype']);
        $post_types = array_map('trim', $post_types);
        $post_types = array_map('sanitize_key', $post_types);
        $post_types = array_filter($post_types);
        if (! empty($post_types)) {
            $query->set('post_type', $post_types);
        }
    } elseif (is_post_type_archive(['landmark', 'petition', 'document'])) {
        $tax_query = [];

        $filterable_taxonomies = [ 'document_type', 'landmark_category', 'combat', 'location', 'keyword' ];

        foreach ($filterable_taxonomies as $taxonomy) {
            $param_name = 'q' . $taxonomy;

            if (isset($_GET[ $param_name ])) {
                $terms = array_map('intval', (array) $_GET[ $param_name ]);

                if (! empty($terms)) {
                    $tax_query[] = [
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $terms,
                    ];
                }
            }
        }

        if (! empty($tax_query)) {
            $query->set('tax_query', $tax_query);
        }
    }
}

if (! is_admin()) {
    add_action('pre_get_posts', 'amnesty_filter_cpt_by_multiple_taxonomies');
}

function apply_tax_location_archive_filters($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_tax('location')) {

        if (isset($_GET['qtype']) && !empty($_GET['qtype'])) {
            $post_types = explode(',', $_GET['qtype']);
            $sanitized_post_types = array_map('sanitize_key', $post_types);

            $query->set('post_type', $sanitized_post_types);
        }

        $tax_query = $query->get('tax_query') ?: [];
        $tax_query['relation'] = 'AND';

        foreach ($_GET as $key => $value) {
            if (strpos($key, 'q') === 0 && $key !== 'qtype' && !empty($value)) {
                $taxonomy = substr($key, 1);
                $terms = array_map('sanitize_key', explode(',', $value));
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $terms,
                ];
            }
        }

        if (count($tax_query) > 1) {
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'apply_tax_location_archive_filters');
