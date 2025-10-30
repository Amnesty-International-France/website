<?php

function amnesty_set_posts_per_page_for_archive($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_archive()) {
        $query->set('posts_per_page', 18);

        if ($query->is_tax('location') && !$query->is_paged()) {

            $query->set('posts_per_page', 17);
        }
    }
}
if (! is_admin()) {
    add_action('pre_get_posts', 'amnesty_set_posts_per_page_for_archive');
}

add_filter('the_posts', function ($posts, $query) {

    if (!is_admin() && $query->is_main_query() && is_tax('location') && !is_paged() && !isset($query->_fiche_pays_injectee)) {

        $term = get_queried_object();
        if (!$term) {
            return $posts;
        }

        $pays_post = get_page_by_path($term->slug, OBJECT, 'fiche_pays');
        if ($pays_post) {
            array_unshift($posts, $pays_post);
            $query->_fiche_pays_injectee = true;
        }
    }
    return $posts;
}, 10, 2);

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

        if (is_post_type_archive('document')) {
            $tax_query[] = [
                'taxonomy' => 'document_type',
                'field'    => 'slug',
                'terms'    => [ 'rapport' ],
            ];
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
        } else {
            $types = get_post_types(['public' => true], 'names');

            $exclude_post_types = [
                'attachment', 'sidebar', 'feedzy_imports', 'feedzy_categories', 'edh', 'fiche_pays',
                'chronique', 'training', 'local-structures', 'document', 'actualities-my-space',
            ];

            $default_post_types = array_filter($types, static fn ($t_name) => ! in_array($t_name, $exclude_post_types, true));

            $query->set('post_type', array_values($default_post_types));
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
