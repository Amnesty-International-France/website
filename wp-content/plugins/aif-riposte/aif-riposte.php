<?php

/*
 * Plugin Name: AIF Riposte
 * Description: A plugin to add Riposte post type
 * Version: 1.0.0
 * Author: Bottoms Up x Tebayo for Amnesty International France
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Plugin constants
 */
define('AIF_RIPOSTE_VERSION', '1.0.0');
define('AIF_RIPOSTE_FILE', __FILE__);
define('AIF_RIPOSTE_PATH', plugin_dir_path(__FILE__));
define('AIF_RIPOSTE_URL', plugin_dir_url(__FILE__));
define('AIF_RIPOSTE_POSTS_PER_PAGE', 5);

/**
 * Plugin includes
 */
require_once AIF_RIPOSTE_PATH . 'includes/post-type.php';
require_once AIF_RIPOSTE_PATH . 'includes/archive.php';
require_once AIF_RIPOSTE_PATH . 'includes/breadcrumb.php';
require_once AIF_RIPOSTE_PATH . 'includes/ajax-load-more.php';
require_once AIF_RIPOSTE_PATH . 'includes/assets.php';
require_once AIF_RIPOSTE_PATH . 'includes/template-loader.php';
require_once AIF_RIPOSTE_PATH . 'includes/card.php';
require_once AIF_RIPOSTE_PATH . 'includes/seo.php';
require_once AIF_RIPOSTE_PATH . 'includes/settings.php';
require_once AIF_RIPOSTE_PATH . 'includes/admin-ordering.php';
require_once AIF_RIPOSTE_PATH . 'includes/admin-taxonomies.php';
require_once AIF_RIPOSTE_PATH . 'includes/metaboxes.php';

/**
 * Activation
 */
register_activation_hook(
	__FILE__,
	static function (): void {
		aif_riposte_register_theme_taxonomy();
		aif_riposte_register_tag_taxonomy();
		aif_riposte_register_post_type();
		flush_rewrite_rules();
	}
);

/**
 * Deactivation
 */
register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);