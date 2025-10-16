<?php

function include_default_template_for_my_space($template)
{
    global $post;

    if (! is_page() || ! $post) {
        return $template;
    }

    $parent_page = get_page_by_path('mon-espace');

    if ($parent_page && in_array($parent_page->ID, get_post_ancestors($post))) {
        $specific_template_php = locate_template("page-{$post->post_name}.php");
        $specific_template_html = locate_template("templates/page-{$post->post_name}.html");

        if ($specific_template_php) {
            return $specific_template_php;
        } elseif ($specific_template_html) {
            set_query_var('html_template_file', $specific_template_html);
            return locate_template('template-html-wrapper.php');
        }

        $default_template = locate_template('templates/page-my-space-default.html');
        if ($default_template) {
            set_query_var('html_template_file', $default_template);
            return locate_template('template-html-wrapper.php');
        }
    }
    return $template;
}


function my_space_access_control()
{
    if (is_admin() || is_preview()) {
        return;
    }

    $my_space_slug = 'mon-espace';

    $is_my_space_page = false;
    if (is_page()) {
        $current_page = get_queried_object();
        $parent_page = get_page_by_path($my_space_slug);

        if ($parent_page) {
            $is_my_space_page = ($current_page->ID === $parent_page->ID || in_array($parent_page->ID, get_post_ancestors($current_page->ID)));
        }
    }

    if (! $is_my_space_page) {
        return;
    }

    check_user_page_access();

    if (!is_user_logged_in()) {
        return;
    }

    $is_member = false;
    if (function_exists('get_salesforce_member_data')) {
        $current_user = wp_get_current_user();
        $sf_member = get_salesforce_member_data($current_user->user_email);
        if (isset($sf_member) && !empty($sf_member->isMembre)) {
            $is_member = true;
        }
    }

    if ($is_member) {
        return;
    }

    $allowed_for_non_members = [
        'mes-dons',
        'mes-informations-personnelles',
        'mes-recus-fiscaux',
        'mes-demandes',
        'mon-compte',
    ];

    $non_member_homepage = home_url('/' . $my_space_slug . '/' . $allowed_for_non_members[0] . '/');
    $current_page_slug = get_post()->post_name;

    if (is_page($my_space_slug)) {
        wp_redirect($non_member_homepage);
        exit;
    }

    if (!in_array($current_page_slug, $allowed_for_non_members)) {
        wp_redirect($non_member_homepage);
        exit;
    }
}

add_action('template_include', 'include_default_template_for_my_space');
add_action('template_redirect', 'my_space_access_control');
