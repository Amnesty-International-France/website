<?php

if ( ! function_exists( 'front_assets_styles' ) ) {
    function front_assets_styles() {
        wp_enqueue_style( 'container', get_stylesheet_directory_uri() . '/assets/styles/container.css', [], wp_get_theme()->get( 'Version' ) );
        wp_enqueue_style( 'article', get_stylesheet_directory_uri() . '/assets/styles/article.css', [], wp_get_theme()->get( 'Version' ) );
        wp_enqueue_style( 'header', get_stylesheet_directory_uri() . '/assets/styles/header.css', [], wp_get_theme()->get( 'Version' ) );
        wp_enqueue_style( 'chip-category', get_stylesheet_directory_uri() . '/assets/styles/chip-category.css', [], wp_get_theme()->get( 'Version' ) );
    }
}

add_action( 'wp_enqueue_scripts', 'front_assets_styles' );

if ( ! function_exists('editor_assets_styles') ) {
    function editor_assets_styles() {
        wp_enqueue_style( 'editor', get_stylesheet_directory_uri() . '/assets/styles/editor.css', [], wp_get_theme()->get( 'Version' ) );
        wp_enqueue_script( 'button', get_stylesheet_directory_uri() . '/assets/scripts/button.js', [], wp_get_theme()->get( 'Version' ) );
    }
}

add_action( 'enqueue_block_editor_assets' , 'editor_assets_styles', 20 );

function block_assets() {
    wp_enqueue_style( 'site', get_stylesheet_directory_uri() . '/assets/styles/site.css', [], wp_get_theme()->get( 'Version' ) );
    wp_enqueue_style('chapo', get_stylesheet_directory_uri() . '/assets/styles/chapo.css', [], wp_get_theme()->get( 'Version' ) );
    wp_enqueue_style('button-styles', get_stylesheet_directory_uri() . '/assets/styles/button-styles.css', [], wp_get_theme()->get( 'Version' ) );
}

add_action( 'enqueue_block_assets', 'block_assets' );

function calculate_reading_time() {
    $content = get_post_field( 'post_content', get_the_ID() );
    $word_count = str_word_count( strip_tags( $content ) );
    $reading_speed = 200;
    return ceil( $word_count / $reading_speed );
}

if ( ! function_exists('amnesty_register_main_menu2') ) {
    function amnesty_register_main_menu2() {
        register_nav_menu( 'main-menu-2', __( 'Main Menu 2', 'child-humanity' ) );
    }
    add_action( 'init', 'amnesty_register_main_menu2' );
}

// Override le theme.json du plugin de branding par le theme.json du child theme
function override_theme_json( $theme_json ) {
    $enfant_theme_json = json_decode( file_get_contents( get_stylesheet_directory(). '/theme.json' ), true );
    $theme_json->update_with($enfant_theme_json);
    return $theme_json;
}

add_filter( 'wp_theme_json_data_theme', 'override_theme_json', 100 );

function register_styles_bouton() {
    register_block_style(
        'core/paragraph',
        [
            'label' => 'Chapo',
            'name' => 'chapo',
            'style_handle' => 'chapo',
        ]
    );
    register_block_style(
        'core/button',
        [
            'label' => 'Yellow',
            'name' => 'yellow',
            'is_default' => true,
            'style_handle' => 'button-styles',
        ]
    );
    register_block_style(
        'core/button',
        [
            'label' => 'Black',
            'name' => 'black',
            'style_handle' => 'button-styles',
        ]
    );
    register_block_style(
        'core/button',
        [
            'label' => 'Black Outline',
            'name' => 'black-outline',
            'style_handle' => 'button-styles',
        ]
    );
    register_block_style(
        'core/button',
        [
            'label' => 'Yellow Outline',
            'name' => 'yellow-outline',
            'style_handle' => 'button-styles',
        ]
    );
}
add_action( 'init', 'register_styles_bouton', 20 );