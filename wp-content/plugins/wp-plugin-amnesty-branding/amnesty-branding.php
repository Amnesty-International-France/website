<?php

/**
 * Plugin Name:       Amnesty International Branding
 * Plugin URI:        https://github.com/amnestywebsite/wp-plugin-amnesty-branding
 * Description:       Applies Amnesty International's branding to the Humanity Theme
 * Version:           2.0.0
 * Author:            Amnesty International
 * Author URI:        https://www.amnesty.org
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aibrand
 * Domain Path:       /languages
 * Network:           true
 * Requires PHP:      8.2.0
 * Requires at least: 6.3.0
 * Tested up to:      6.6.2
 */

declare( strict_types = 1 );

namespace Amnesty\Branding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIBRAND_DIR', __DIR__ );
define( 'AIBRAND_FILE', __FILE__ );
define( 'AIBRAND_PATTERNS_DIR', untrailingslashit( AIBRAND_DIR ) . '/views/patterns/' );

require_once AIBRAND_DIR . '/helpers/cmb2-helpers.php';
require_once AIBRAND_DIR . '/helpers/post-single.php';

require_once AIBRAND_DIR . '/includes/class-events.php';
require_once AIBRAND_DIR . '/includes/class-plugin.php';

// Core Block Filters
require_once dirname( AIBRAND_FILE ) . '/includes/editor/core-blocks/gallery/filters.php';

// Core Block Filters
require_once dirname( AIBRAND_FILE ) . '/includes/editor/core-blocks/gallery/filters.php';

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

register_activation_hook( __FILE__, [ Events::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Events::class, 'deactivate' ] );

add_action( 'plugins_loaded', [ Plugin::class, 'init' ], 1 );
