<?php

function search_resources_by_taxonomy(WP_REST_Request $request, string $taxonomy)
{
    $term = $request->get_param('term');

    $args = [
        'post_type' => 'document',
        'posts_per_page' => 5,
        'tax_query' => [
            [
                'taxonomy' => $taxonomy,
                'operator' => 'EXISTS',
            ],
        ],
        's' => $term,
    ];

    $query = new WP_Query($args);
    $results = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = [
                'title' => str_replace('&#8211;', '-', get_the_title()),
                'link' => get_permalink(),
            ];
        }
    }

    return $results;
}

function rest_search_document_militant(WP_REST_Request $request)
{
    return rest_ensure_response(search_resources_by_taxonomy($request, 'document_militant_type'));
}

function rest_search_document_democratic(WP_REST_Request $request)
{
    return rest_ensure_response(search_resources_by_taxonomy($request, 'document_democratic_type'));
}

add_action('rest_api_init', function () {
    register_rest_route('humanity/v1', '/search-document-militant', [
        'methods' => 'GET',
        'callback' => 'rest_search_document_militant',
        'permission_callback' => '__return_true',
        'args' => [
            'term' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);

    register_rest_route('humanity/v1', '/search-document-democratic', [
        'methods' => 'GET',
        'callback' => 'rest_search_document_democratic',
        'permission_callback' => '__return_true',
        'args' => [
            'term' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});
