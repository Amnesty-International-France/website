<?php

if ( ! function_exists( 'featured_image_styles' ) ) {
    function featured_image_styles() {
        $theme = wp_get_theme();
        wp_enqueue_style( 'featured-image', get_stylesheet_directory_uri() . '/assets/styles/test1.css', [], $theme->get( 'Version' ), 'all');
    }
}

add_action( 'wp_enqueue_scripts', 'featured_image_styles' );

function calculate_reading_time() {
    $content = get_post_field( 'post_content', get_the_ID() );
    $word_count = str_word_count( strip_tags( $content ) );
    $reading_speed = 200;
    return ceil( $word_count / $reading_speed );
}