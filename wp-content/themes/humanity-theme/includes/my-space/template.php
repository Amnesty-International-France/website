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

    if (is_page() && ! is_preview()) {
        $current_page = get_queried_object();

        $parent_page = get_page_by_path($slug_parent_page);

        if ($parent_page) {
            $id_parent_page = $parent_page->ID;

            $ancestors = get_post_ancestors($current_page->ID);

            if ($current_page->ID === $id_parent_page || in_array($id_parent_page, $ancestors)) {
                check_user_page_access();
            }
        }
    }
}

add_action('template_redirect', 'aif_restrict_my_space_access');

function aif_restrict_my_space_access()
{
    $my_space_parent_slug = 'mon-espace';

    $allowed_for_non_members = [
        'mes-dons',
        'mes-informations-personnelles',
        'mes-recus-fiscaux',
        'mes-demandes',
        'mon-compte',
    ];

    if (!is_user_logged_in() || is_admin()) {
        return;
    }

    if (!is_page($my_space_parent_slug) && !get_post_ancestors(get_the_ID())) {
        $post = get_post(get_the_ID());
        $parent = get_post($post->post_parent);
        if (!$parent || $parent->post_name !== $my_space_parent_slug) {
            return;
        }
    }

    $is_member = false;
    $current_user = wp_get_current_user();

    if (function_exists('get_salesforce_member_data')) {
        $sf_member = get_salesforce_member_data($current_user->user_email);

        if (isset($sf_member) && !empty($sf_member->isMembre)) {
            $is_member = true;
        }
    }

    if ($is_member) {
        return;
    }

    $non_member_homepage = home_url('/' . $my_space_parent_slug . '/' . $allowed_for_non_members[0] . '/');

    if (is_page($my_space_parent_slug)) {
        wp_redirect($non_member_homepage);
        exit;
    }

    $current_page_slug = get_post()->post_name;
    if (!in_array($current_page_slug, $allowed_for_non_members)) {
        wp_redirect($non_member_homepage);
        exit;
    }
}
