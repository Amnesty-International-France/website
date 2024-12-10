<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

/**
 * Event calls
 */
final class Events {

	/**
	 * Plugin activation event
	 *
	 * @return void
	 */
	public static function activate(): void {
		$current_theme = wp_get_theme();

		// this plugin requires the Amnesty WP Theme/Humanity Theme
		if ( in_array( $current_theme['Name'], [ 'Amnesty WP Theme', 'Amnesty WP Child Theme', 'Humanity Theme' ], true ) ) {
			return;
		}

		deactivate_plugins( AIBRAND_FILE, true, self::is_network_active( AIBRAND_FILE ) );
	}

	/**
	 * Plugin deactivation event
	 *
	 * @return void
	 */
	public static function deactivate(): void {
	}

	/**
	 * Check whether plugin is network-active
	 *
	 * @param string $abspath the absolute path to the plugin file
	 *
	 * @return bool
	 */
	protected static function is_network_active( string $abspath ): bool {
		$relpath = sprintf( '%s/%s', basename( dirname( $abspath ) ), basename( $abspath ) );
		return is_multisite() && is_plugin_active_for_network( $relpath );
	}

}
