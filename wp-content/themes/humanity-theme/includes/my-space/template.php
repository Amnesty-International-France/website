<?php

function assign_myspace_template_to_descendants( $post_id, $post, $update ) {
    if ( wp_is_post_revision( $post_id ) || $post->post_type !== 'page' ) {
        return;
    }

    $parent_page_object = get_page_by_path( 'mon-espace' );

    if ( ! $parent_page_object ) {
        return;
    }

    $ancestors = get_post_ancestors( $post_id );

    if ( ! empty($ancestors) && in_array( $parent_page_object->ID, $ancestors ) ) {
        $template_file = 'templates/page-my-space-default.html';

        $current_template = get_page_template_slug( $post_id );

        if ( $template_file !== $current_template ) {
            update_post_meta( $post_id, '_wp_page_template', $template_file );
        }
    }
}

add_action( 'save_post', 'assign_myspace_template_to_descendants', 10, 3 );
