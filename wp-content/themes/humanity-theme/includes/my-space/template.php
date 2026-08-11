<?php

function include_default_template_for_my_space($template)
{
    global $post;

    if (! is_page() || !$post) {
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
add_action('template_include', 'include_default_template_for_my_space');

add_action('template_redirect', 'auth_my_space');

function auth_my_space()
{
    $slug_parent_page = 'mon-espace';

    $current_page = get_queried_object();
    $parent_page = get_page_by_path($slug_parent_page);

    if (!$current_page || !$parent_page) {
        return;
    }

    $is_my_space_page = false;

    if (is_page()) {
        $ancestors = get_post_ancestors($current_page->ID);
        $is_my_space_page = $current_page->ID === $parent_page->ID
            || in_array($parent_page->ID, $ancestors, true);
    }

    if (!$is_my_space_page && !aif_is_my_space_single()) {
        return;
    }

    $sf_member = check_user_page_access();

    set_query_var('aif_salesforce_member', $sf_member);

    if (is_preview()) {
        return;
    }

    aif_restrict_my_space_access($sf_member, $current_page, $parent_page);
}

function aif_is_my_space_single()
{
    return is_singular('actualities-my-space')
        || (is_singular('training') && get_query_var('is_my_space_training'))
        || (is_singular('petition') && get_query_var('is_my_space_petition'));
}

add_filter('logout_redirect', 'aif_my_space_logout_redirect', 10, 2);

function aif_my_space_logout_redirect($redirect_to, $requested_redirect_to)
{
    if (!empty($requested_redirect_to)) {
        return $redirect_to;
    }

    return home_url('/');
}

function aif_restrict_my_space_access($sf_member, $current_page, $parent_page)
{
    if (current_user_can('manage_options')) {
        return;
    }

    $allowed_for_non_members = [
        'mes-dons',
        'mes-informations-personnelles',
        'mes-recus-fiscaux',
        'mes-demandes',
        'nous-contacter',
        'mon-compte',
        'se-deconnecter',
    ];

    if (!empty($sf_member->isMembre)) {
        return;
    }

    $non_member_homepage = home_url('/mon-espace/' . $allowed_for_non_members[0] . '/');

    if ($current_page->ID === $parent_page->ID) {
        wp_redirect($non_member_homepage);
        exit;
    }

    if (!in_array($current_page->post_name, $allowed_for_non_members)) {
        wp_redirect($non_member_homepage);
        exit;
    }
}
