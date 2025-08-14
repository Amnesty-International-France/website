<?php

add_filter( 'wpseo_breadcrumb_links', 'my_space_custom_breadcrumb' );

function my_space_custom_breadcrumb( $links ) {
    global $post;

    if ( ! isset( $post ) ) {
        return $links;
    }

    $page_mon_espace = get_page_by_path('mon-espace');

    if ( $page_mon_espace && in_array( $page_mon_espace->ID, get_post_ancestors( $post ) ) ) {
        array_shift( $links );
    }

    return $links;
}
