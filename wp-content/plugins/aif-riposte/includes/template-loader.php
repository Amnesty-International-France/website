<?php

/**
 * Riposte victory template loader.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Use plugin archive template for Riposte Victory archive.
 *
 * @param string $template Current template path.
 *
 * @return string
 */
function aif_riposte_archive_template(string $template): string
{
	if (! is_post_type_archive('riposte_victory')) {
		return $template;
	}

	$plugin_template = AIF_RIPOSTE_PATH . 'templates/archive-riposte-victory.php';

	if (file_exists($plugin_template)) {
		return $plugin_template;
	}

	return $template;
}
add_filter('template_include', 'aif_riposte_archive_template');


/**
 * No single Riposte - Redirect to Riposte archive
 */
function aif_riposte_disable_single(): void
{
	if (! is_singular('riposte_victory')) {
		return;
	}

	wp_safe_redirect(get_post_type_archive_link('riposte_victory'), 301);
	exit;
}
add_action('template_redirect', 'aif_riposte_disable_single');