<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

use WP_Block_Patterns_Registry;

/**
 * Handles loading FSE block patterns from the plugin.
 *
 * Core doesn't support loading patterns from plugins
 * by default, so we have to emulate their loading
 * process for patterns from themes.
 */
class Patterns_Loader {

	/**
	 * Allowed pattern header comment headers
	 *
	 * @var array<string,string>
	 */
	protected static array $default_headers = [
		'title'         => 'Title',
		'slug'          => 'Slug',
		'description'   => 'Description',
		'viewportWidth' => 'Viewport Width',
		'inserter'      => 'Inserter',
		'categories'    => 'Categories',
		'keywords'      => 'Keywords',
		'blockTypes'    => 'Block Types',
		'postTypes'     => 'Post Types',
		'templateTypes' => 'Template Types',
	];

	/**
	 * Pattern header keys that require parsing
	 *
	 * @var array<int,string>
	 */
	protected static array $properties_to_parse = [
		'categories',
		'keywords',
		'blockTypes',
		'postTypes',
		'templateTypes',
	];

	/**
	 * The plugin data
	 *
	 * @var array<string,mixed>
	 */
	protected static array $plugin_data = [];

	/**
	 * The patterns directory to load from
	 *
	 * @var string
	 */
	protected string $patterns_dir = '';

	/**
	 * Constructor
	 *
	 * @param string $plugin_file  the plugin entry point
	 * @param string $patterns_dir the patterns directory path
	 */
	public function __construct( string $plugin_file, string $patterns_dir ) {
		$this->patterns_dir  = trailingslashit( $patterns_dir );
		static::$plugin_data = get_plugin_data( $plugin_file, false, false );

		add_action( 'admin_init', [ $this, 'register_plugin_patterns' ] );
	}

	/**
	 * Register all found plugin patterns
	 *
	 * @return void
	 */
	public function register_plugin_patterns(): void {
		$registry = WP_Block_Patterns_Registry::get_instance();
		$patterns = $this->get_plugin_patterns();

		foreach ( $patterns as $file => $pattern_data ) {
			if ( $registry->is_registered( $pattern_data['slug'] ) ) {
				continue;
			}

			$file_path = $this->patterns_dir . $file;

			if ( ! $this->file_exists( $file_path ) ) {
				continue;
			}

			$pattern_data['content'] = $this->get_content( $file_path );

			if ( ! $pattern_data['content'] ) {
				continue;
			}

			$pattern_data = $this->translate_pattern( $pattern_data );

			register_block_pattern( $pattern_data['slug'], $pattern_data );
		}
	}

	/**
	 * Check pattern file exists
	 *
	 * Worth checking as patterns are loaded from cache
	 *
	 * @param string $file the pattern file
	 *
	 * @return bool
	 */
	protected function file_exists( string $file ): bool {
		if ( ! file_exists( $file ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: %s: file name. */
					esc_html__( 'Could not register file "%s" as a block pattern as the file does not exist.' ),
					esc_html( $file )
				),
				'6.4.0'
			);

			$this->delete_pattern_cache();

			return false;
		}

		return true;
	}

	/**
	 * Get a pattern's file contents
	 *
	 * @param string $file the pattern file
	 *
	 * @return string
	 */
	protected function get_content( string $file ): string {
		ob_start();
		include $file;
		return ob_get_clean();
	}

	/**
	 * Translate translatable pattern data
	 *
	 * @param array<string,mixed> $pattern the pattern data
	 *
	 * @return array<string,mixed>
	 */
	protected function translate_pattern( array $pattern ): array {
		// phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
		$pattern['title'] = translate_with_gettext_context(
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			$pattern['title'],
			'Pattern title',
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
			static::$plugin_data['TextDomain']
		);

		if ( ! isset( $pattern['description'] ) || ! trim( strval( $pattern['description'] ) ) ) {
			return $pattern;
		}

		// phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
		$pattern['description'] = translate_with_gettext_context(
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			$pattern['description'],
			'Pattern description',
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
			static::$plugin_data['TextDomain'],
		);

		return $pattern;
	}

	/**
	 * Retrieve parsed list of valid patterns
	 *
	 * @return array<string,array<string,mixed>>
	 */
	protected function get_plugin_patterns(): array {
		$can_use_cached = ! wp_is_development_mode( 'plugin' );
		$pattern_data   = $this->get_pattern_cache();

		if ( is_array( $pattern_data ) ) {
			if ( $can_use_cached ) {
				return $pattern_data;
			}

			// If in development mode, clear pattern cache.
			$this->delete_pattern_cache();
		}

		$pattern_files = $this->get_pattern_files();

		foreach ( $pattern_files as $file ) {
			$pattern = $this->parse_pattern( $file );

			if ( ! $this->is_valid_pattern( $file, $pattern ) ) {
				continue;
			}

			$key = str_replace( $this->patterns_dir, '', $file );

			$pattern_data[ $key ] = $pattern;
		}

		if ( $can_use_cached ) {
			$this->set_pattern_cache( $pattern_data );
		}

		return $pattern_data;
	}

	/**
	 * Retrieve files from the patterns dir
	 *
	 * @return array<int,string>
	 */
	protected function get_pattern_files(): array {
		$can_use_cached = ! wp_is_development_mode( 'plugin' );
		$pattern_data   = [];

		if ( ! file_exists( $this->patterns_dir ) ) {
			if ( $can_use_cached ) {
				$this->set_pattern_cache( $pattern_data );
			}

			return $pattern_data;
		}

		$files = glob( $this->patterns_dir . '*.php' );
		if ( ! $files ) {
			if ( $can_use_cached ) {
				$this->set_pattern_cache( $pattern_data );
			}

			return $pattern_data;
		}

		return $files;
	}

	/**
	 * Checks that required pattern file headers are set and valid
	 *
	 * @param string              $file    the file path to the pattern
	 * @param array<string,mixed> $pattern the pattern data
	 *
	 * @return bool
	 */
	protected function is_valid_pattern( string $file, array $pattern ): bool {
		if ( ! isset( $pattern['slug'] ) || ! trim( strval( $pattern['slug'] ) ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: file name. */
					esc_html__( 'Could not register file "%s" as a block pattern ("Slug" field missing)' ),
					esc_html( $file )
				),
				'6.0.0'
			);

			return false;
		}

		if ( ! preg_match( '/^[A-z0-9\/_-]+$/', $pattern['slug'] ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: file name; 2: slug value found. */
					esc_html__( 'Could not register file "%1$s" as a block pattern (invalid slug "%2$s")' ),
					esc_html( $file ),
					esc_html( $pattern['slug'] )
				),
				'6.0.0'
			);

			return false;
		}

		// Title is a required property.
		if ( ! isset( $pattern['title'] ) || ! trim( strval( $pattern['title'] ) ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: file name. */
					esc_html__( 'Could not register file "%s" as a block pattern ("Title" field missing)' ),
					esc_html( $file )
				),
				'6.0.0'
			);

			return false;
		}

		return true;
	}

	/**
	 * Parse pattern header data
	 *
	 * @param string $file the pattern file
	 *
	 * @return array<string,mixed>
	 */
	protected function parse_pattern( string $file ): array {
		$pattern = get_file_data( $file, static::$default_headers );

		// cleanup
		foreach ( $pattern as $key => $value ) {
			if ( ! trim( strval( $value ) ) ) {
				unset( $pattern[ $key ] );
			}
		}

		// For properties of type array, parse data as comma-separated.
		foreach ( static::$properties_to_parse as $property ) {
			if ( ! isset( $pattern[ $property ] ) || ! trim( strval( $pattern[ $property ] ) ) ) {
				continue;
			}

			$value = wp_parse_list( (string) $pattern[ $property ] );
			$value = array_values( array_filter( $value ) );

			$pattern[ $property ] = $value;
		}

		if ( array_key_exists( 'viewportWidth', $pattern ) ) {
			$pattern['viewportWidth'] = (int) $pattern['viewportWidth'];
		}

		if ( array_key_exists( 'inserter', $pattern ) ) {
			$pattern['inserter'] = in_array( strtolower( $pattern['inserter'] ), [ 'yes', 'true' ], true );
		}

		return $pattern;
	}

	/**
	 * Gets block pattern cache.
	 *
	 * @return array|null
	 */
	protected function get_pattern_cache(): ?array {
		$plugin_slug  = sanitize_key( static::$plugin_data['Name'] );
		$pattern_data = wp_cache_get( 'wp_plugin_patterns_' . $plugin_slug );

		if ( is_array( $pattern_data ) && $pattern_data['version'] === static::$plugin_data['Version'] ) {
			return $pattern_data['patterns'];
		}

		return null;
	}

	/**
	 * Sets block pattern cache.
	 *
	 * @param array $patterns Block patterns data to set in cache.
	 *
	 * @return void
	 */
	private function set_pattern_cache( array $patterns ): void {
		$pattern_data = [
			'version'  => static::$plugin_data['Version'],
			'patterns' => $patterns,
		];

		$plugin_slug = sanitize_key( static::$plugin_data['Name'] );
		wp_cache_set( 'wp_plugin_patterns_' . $plugin_slug, $pattern_data );
	}

	/**
	 * Clears block pattern cache.
	 *
	 * @return void
	 */
	protected function delete_pattern_cache(): void {
		$plugin_slug = sanitize_key( static::$plugin_data['Name'] );
		wp_cache_delete( 'wp_plugin_patterns_' . $plugin_slug );
	}

}
