<?php

function title_filter($where, $wp_query)
{
    global $wpdb;
    if ($search_term = $wp_query->get('search_title')) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like($search_term) . '%\'';
    }
    return $where;
}

function search_resources_by_taxonomies(WP_REST_Request $request, array $taxonomies)
{
    add_filter('posts_where', 'title_filter', 10, 2);
    $term = $request->get_param('term');

    $tax_query = [];
    foreach ($taxonomies as $taxonomy) {
        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'operator' => 'EXISTS',
        ];
    }

    $args = [
        'post_type'      => 'document',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'tax_query'      => $tax_query,
        'meta_query'     => [
            [
                'key'     => 'document_private',
                'value'   => true,
                'compare' => '=',
            ],
        ],
        'search_title'   => $term,
    ];

    $query   = new WP_Query($args);
    remove_filter('posts_where', 'title_filter', 10);

    $results = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = [
                'title' => str_replace('&#8211;', '-', get_the_title()),
                'link'  => get_permalink(),
            ];
        }
    }

    return $results;
}

function rest_search_document_militant(WP_REST_Request $request)
{
    return rest_ensure_response(search_resources_by_taxonomies($request, ['document_militant_type']));
}

function rest_search_document_democratic(WP_REST_Request $request)
{
    return rest_ensure_response(search_resources_by_taxonomies($request, ['document_democratic_type', 'document_instance_type']));
}

add_action(
    'rest_api_init',
    function () {
        register_rest_route(
            'humanity/v1',
            '/search-document-militant',
            [
                'methods'             => 'GET',
                'callback'            => 'rest_search_document_militant',
                'permission_callback' => '__return_true',
                'args'                => [
                    'term' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        register_rest_route(
            'humanity/v1',
            '/search-document-democratic',
            [
                'methods'             => 'GET',
                'callback'            => 'rest_search_document_democratic',
                'permission_callback' => '__return_true',
                'args'                => [
                    'term' => [
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }
);
