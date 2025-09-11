<?php

function include_default_template_for_my_space( $template ) {
	global $post;

	if ( ! is_page() || !$post ) {
		return $template;
	}

	$parent_page = get_page_by_path('mon-espace');

	if ( $parent_page && in_array( $parent_page->ID, get_post_ancestors( $post ) ) ) {
		$specific_template_php = locate_template("page-{$post->post_name}.php");
		$specific_template_html = locate_template("templates/page-{$post->post_name}.html");

		if ($specific_template_php) {
			return $specific_template_php;
		} elseif ($specific_template_html) {
			set_query_var('html_template_file', $specific_template_html);
			return locate_template('template-html-wrapper.php');
		}

		$default_template = locate_template("templates/page-my-space-default.html");
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
