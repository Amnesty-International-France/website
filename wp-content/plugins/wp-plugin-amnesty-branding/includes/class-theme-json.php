<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

use WP_Theme_JSON_Data;

/**
 * Helper class for overriding theme.json
 */
class Theme_JSON {

	/**
	 * `theme.json` file cache.
	 *
	 * @var array<string,mixed>
	 */
	protected static $theme_json_file_cache = [];

	/**
	 * Container to keep loaded i18n schema for `theme.json`.
	 *
	 * @var array|null
	 */
	protected static $i18n_schema = null;

	/**
	 * Read theme.json overrides from this plugin
	 *
	 * @param string $file_path the path to the override theme.json
	 *
	 * @return array
	 */
	public static function get_overload_data( string $file_path ): array {
		$config = static::read_json_file( $file_path );
		$config = static::rewrite_theme_json_relpath( $config );
		$config = static::translate( $config );
		$config = new WP_Theme_JSON_Data( $config, 'theme' );

		return $config->get_data();
	}

	/**
	 * Rewrite relpath placeholders in JSON
	 *
	 * @param array $data the JSON data
	 *
	 * @return array
	 */
	protected static function rewrite_theme_json_relpath( array $data ): array {
		$relpath   = static::get_plugin_dir_url();
		$rewritten = [];

		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$rewritten[ $key ] = str_replace( '{PLUGIN_DIR_URL}', $relpath, $value );
				continue;
			}

			if ( ! is_array( $value ) ) {
				$rewritten[ $key ] = $value;
				continue;
			}

			$rewritten[ $key ] = static::rewrite_theme_json_relpath( $value );
		}

		return $rewritten;
	}

	/**
	 * Retrieve URI to plugin dir
	 *
	 * @return string
	 */
	protected static function get_plugin_dir_url(): string {
		$dir = basename( dirname( AIBRAND_FILE ) );
		$dir = WP_PLUGIN_DIR . '/' . $dir;
		$dir = '/' . str_replace( ABSPATH, '', $dir );

		return home_url( $dir );
	}

	/**
	 * Processes a file that adheres to the theme.json schema
	 * and returns an array with its contents, or a void array if none found.
	 *
	 * @param string $file_path Path to file. Empty if no file.
	 *
	 * @return array Contents that adhere to the theme.json schema.
	 */
	protected static function read_json_file( string $file_path ): array {
		if ( ! $file_path ) {
			return [];
		}

		if ( array_key_exists( $file_path, static::$theme_json_file_cache ) ) {
			return static::$theme_json_file_cache[ $file_path ];
		}

		$decoded_file = wp_json_file_decode( $file_path, [ 'associative' => true ] );

		if ( ! is_array( $decoded_file ) ) {
			return [];
		}

		static::$theme_json_file_cache[ $file_path ] = $decoded_file;

		return static::$theme_json_file_cache[ $file_path ];
	}

	/**
	 * Given a theme.json structure modifies it in place to update certain values
	 * by its translated strings according to the language set by the user.
	 *
	 * @param array  $theme_json The theme.json to translate.
	 * @param string $domain     Optional. Text domain. Default 'default'.
	 *
	 * @return array Returns the modified $theme_json_structure.
	 */
	protected static function translate( array $theme_json, string $domain = 'aibrand' ): array {
		if ( null !== static::$i18n_schema ) {
			return translate_settings_using_i18n_schema( static::$i18n_schema, $theme_json, $domain );
		}

		$i18n_schema = wp_json_file_decode( ABSPATH . WPINC . '/theme-i18n.json' );

		static::$i18n_schema = $i18n_schema ?? [];

		return translate_settings_using_i18n_schema( static::$i18n_schema, $theme_json, $domain );
	}

}
