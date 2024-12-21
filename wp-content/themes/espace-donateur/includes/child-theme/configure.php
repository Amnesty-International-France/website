<?php



function humanity_theme_parent_theme_enqueue_styles()
{

    wp_enqueue_style('humanity-theme-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'espace-donateur-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['humanity-theme-style']
    );

}
add_action('wp_enqueue_scripts', 'humanity_theme_parent_theme_enqueue_styles');

function humanity_theme_parent_theme_enqueue_scripts()
{
    wp_enqueue_script('check-password-js', get_stylesheet_directory_uri().'/js/check-password.js');
}

add_action('wp_enqueue_scripts', 'humanity_theme_parent_theme_enqueue_scripts');

function hide_admin_bar_for_limited_users()
{
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_bar_for_limited_users');


function restrict_admin_access()
{
    if (is_admin() && !current_user_can('administrator') && !defined('DOING_AJAX')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('init', 'restrict_admin_access');
