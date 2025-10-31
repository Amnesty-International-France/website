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

        if ($query->is_tax('combat') && !$query->is_paged()) {
            $query->set('posts_per_page', 17);
        }
    }
}
if (!is_admin()) {
    add_action('pre_get_posts', 'amnesty_set_posts_per_page_for_archive');
}

add_filter('the_posts', function ($posts, $query) {
    if (!is_admin() && $query->is_main_query()) {

        if (is_tax('location') && !is_paged() && !isset($query->_fiche_pays_injectee)) {
            $term = get_queried_object();
            if ($term) {
                $pays_post = get_page_by_path($term->slug, OBJECT, 'fiche_pays');
                if ($pays_post) {
                    array_unshift($posts, $pays_post);
                    $query->_fiche_pays_injectee = true;
                }
            }
        }

        if (is_tax('combat') && !is_paged() && !isset($query->_fiche_combat_injectee)) {
            $term = get_queried_object();
            if ($term) {
                $combat_page = get_page_by_path('nos-combats/' . $term->slug);
                if ($combat_page) {
                    array_unshift($posts, $combat_page);
                    $query->_fiche_combat_injectee = true;
                }
            }
        }
    }

    return $posts;
}, 10, 2);


function amnesty_filter_cpt_by_multiple_taxonomies(WP_Query $query)
{
    if (! $query->is_main_query()) {
        return;
    }

    if ($query->is_tax()) {
        if (isset($_GET['qtype'])) {
            $post_types = explode(',', $_GET['qtype']);
            $post_types = array_map('trim', $post_types);
            $post_types = array_map('sanitize_key', $post_types);
            $post_types = array_filter($post_types);
        } else {
            $post_types = ['post', 'press-release', 'petition', 'rapport', 'landmark'];
        }

        if (in_array('rapport', $post_types, true)) {
            unset($post_types[array_search('rapport', $post_types, true)]);
            $location = $query->get('location', null);
            $combat = $query->get('combat', null);
            if (isset($location)) {
                $posts_ids = !empty($post_types) ? handle_post_types_filter($post_types, 'location', $location) : [];
                $rapports_ids = handle_rapport_filter('location', $location);
            } elseif (isset($combat)) {
                $posts_ids = !empty($post_types) ? handle_post_types_filter($post_types, 'combat', $combat) : [];
                $rapports_ids = handle_rapport_filter('combat', $combat);
            }
            $combined = array_merge($posts_ids ?? [], $rapports_ids ?? []);
            $query->set('post__in', $combined);
            $post_types[] = 'document';
        }

        if (!empty($post_types)) {
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

function handle_post_types_filter($post_types, $taxonomy, $term)
{
    $query = new WP_Query([
        'post_type' => $post_types,
        'fields' => 'ids',
        'posts_per_page' => -1,
        $taxonomy => $term,
    ]);
    if ($query->have_posts()) {
        return $query->posts;
    }
    return [];
}
function handle_rapport_filter($taxonomy, $term)
{
    $query = new WP_Query([
        'post_type' => 'document',
        'fields' => 'ids',
        'posts_per_page' => -1,
        $taxonomy => $term,
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'document_type',
                'field' => 'slug',
                'terms' => [ 'rapport' ],
            ],
        ],
    ]);
    if ($query->have_posts()) {
        return $query->posts;
    }

    return [];
}
