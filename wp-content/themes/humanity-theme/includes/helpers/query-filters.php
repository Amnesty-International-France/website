<?php

function amnesty_filter_cpt_by_multiple_taxonomies(WP_Query $query)
{
    if (is_admin() || ! $query->is_main_query()) {
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
    } elseif (is_post_type_archive(['landmark', 'petition'])) {
        $tax_query = [];

        $filterable_taxonomies = [ 'landmark_category', 'combat', 'location', 'keyword' ];

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

add_action('pre_get_posts', 'amnesty_filter_cpt_by_multiple_taxonomies');
