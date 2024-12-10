<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

use DateTimeZone;
use WP_Theme_JSON_Data;

/**
 * Loader for scripts/styles
 */
class Asset_Loader {

	/**
	 * Parsed data for this plugin
	 *
	 * @var array<string,mixed>
	 */
	protected array $plugin_data;

	/**
	 * Bind hooks
	 */
	public function __construct() {
		$this->plugin_data = get_plugin_data( AIBRAND_FILE );

		add_filter( 'wp_theme_json_data_theme', [ $this, 'override_theme_json_data' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Override JSON data for the theme
	 *
	 * @param \WP_Theme_JSON_Data $theme_json the theme.json object
	 *
	 * @return \WP_Theme_JSON_Data
	 */
	public function override_theme_json_data( WP_Theme_JSON_Data $theme_json ): WP_Theme_JSON_Data {
		$overrides = Theme_JSON::get_overload_data( dirname( AIBRAND_FILE ) . '/theme.json' );

		$theme_json->update_with( $overrides );

		return $theme_json;
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts(): void {
		$style_deps  = [ 'amnesty-theme' ];
		$script_deps = [];

		wp_enqueue_style(
			'aibrand-frontend',
			plugins_url( '/assets/styles/frontend.css', AIBRAND_FILE ),
			$style_deps,
			$this->plugin_data['Version'],
			'all'
		);

		wp_enqueue_script(
			'aibrand-frontend',
			plugins_url( '/assets/scripts/frontend.js', AIBRAND_FILE ),
			$script_deps,
			$this->plugin_data['Version'],
			true,
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts(): void {
		$style_deps  = [ 'theme-admin' ];
		$script_deps = [];

		wp_enqueue_style(
			'aibrand-admin',
			plugins_url( '/assets/styles/admin.css', AIBRAND_FILE ),
			$style_deps,
			$this->plugin_data['Version'],
			'all'
		);

		wp_enqueue_script(
			'aibrand-admin',
			plugins_url( '/assets/scripts/admin.js', AIBRAND_FILE ),
			$script_deps,
			$this->plugin_data['Version'],
			true,
		);
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_assets(): void {
		$style_deps  = [];
		$script_deps = [];

		wp_enqueue_style(
			'aibrand-site-editor',
			plugins_url( '/assets/styles/siteeditor.css', AIBRAND_FILE ),
			$style_deps,
			$this->plugin_data['Version'],
			'all'
		);

		wp_enqueue_script(
			'aibrand-site-editor',
			plugins_url( '/assets/scripts/siteeditor.js', AIBRAND_FILE ),
			$script_deps,
			$this->plugin_data['Version'],
			true,
		);

		wp_set_script_translations(
			'aibrand-site-editor',
			'aibrand',
			plugin_dir_path( AIBRAND_FILE ) . 'languages',
		);
	}

	/**
	 * Enqueue block assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		$style_deps  = [ 'amnesty-core-gutenberg' ];
		$script_deps = [];

		wp_enqueue_style(
			'aibrand-block-editor',
			plugins_url( '/assets/styles/blockeditor.css', AIBRAND_FILE ),
			$style_deps,
			$this->plugin_data['Version'],
			'all'
		);

		wp_enqueue_script(
			'aibrand-block-editor',
			plugins_url( '/assets/scripts/blockeditor.js', AIBRAND_FILE ),
			$script_deps,
			$this->plugin_data['Version'],
			true,
		);

		wp_set_script_translations(
			'aibrand-block-editor',
			'aibrand',
			plugin_dir_path( AIBRAND_FILE ) . 'languages',
		);

		$this->localise_block_editor_assets( 'aibrand-block-editor' );
	}

	/**
	 * Provide data to the block editor assets
	 *
	 * @param string $handle the asset handle
	 *
	 * @return void
	 */
	protected function localise_block_editor_assets( string $handle ): void {
		$this->localise_timeinfo( $handle );
	}

	/**
	 * Provide server and setting timezones
	 *
	 * @param string $handle the asset handle
	 *
	 * @return void
	 */
	protected function localise_timeinfo( string $handle ): void {
		$wordpress_tz   = wp_timezone();
		$wordpress_time = wp_date( 'Y-m-d H:i:s', null, $wordpress_tz );
		$server_tz      = new DateTimeZone( date_default_timezone_get() );
		$server_time    = wp_date( 'Y-m-d H:i:s', null, $server_tz );

		wp_localize_script(
			$handle,
			'datetimeInfo',
			[
				'wordpress' => [
					'time' => $wordpress_time,
					'zone' => $wordpress_tz->getName(),
				],
				'server'    => [
					'time' => $server_time,
					'zone' => $server_tz->getName(),
				],
			]
		);
	}

}
