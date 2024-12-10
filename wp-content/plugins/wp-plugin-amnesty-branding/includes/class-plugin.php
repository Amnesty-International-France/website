<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

/**
 * Plugin instantiation
 */
final class Plugin {

	/**
	 * Instantiate plugin
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::should_init() ) {
			new self();
		}
	}

	/**
	 * Whether to allow the plugin to initialise
	 *
	 * @return bool
	 */
	protected static function should_init(): bool {
		if ( ! function_exists( 'wp_get_development_mode' ) ) {
			return false;
		}

		if ( in_array( wp_get_development_mode(), [ 'all', 'plugin' ], true ) ) {
			return true;
		}

		$current_theme = wp_get_theme();

		return 'Humanity Theme' === $current_theme['Name'];
	}

	/**
	 * Construct the class
	 */
	protected function __construct() {
		$this->load_files();

		new Asset_Loader();
		new Post_Meta();
		new Block_Template_Loader();
		new Patterns_Loader( AIBRAND_FILE, AIBRAND_PATTERNS_DIR );
		new Template_Loader();

		new Frontend\Post_Content();
		new Frontend\Post_Single();

		new RSS\RSS_Feed_Filters();

		new Seo\Yoast_Redirects();
	}

	/**
	 * Load required files
	 *
	 * @return void
	 */
	protected function load_files(): void {
		require_once dirname( AIBRAND_FILE ) . '/includes/class-asset-loader.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/class-theme-json.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/class-post-meta.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/class-block-template-loader.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/class-patterns-loader.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/class-template-loader.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/admin/media-library/columns.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/admin/theme-options/contact.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/admin/woocommerce/product-general-options.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/editor/blocks/unregister.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/editor/block-patterns/unregister.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/blocks/recipients/register.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/blocks/restrictions.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/features/death-penalty/helpers.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/death-penalty/taxonomy-custom-fields.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/language-selector/helpers.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/language-selector/render.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/letter-recipients/metadata.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/petitions/meta.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/features/petitions/render.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/frontend/class-post-single.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/frontend/class-post-content.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/frontend/navigation.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/frontend/scripts-and-styles.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/rss/author.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/rss/class-rss-feed-filters.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/rss/namespace.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/taxonomies/filters.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/theme-setup/wp-head.php';

		require_once dirname( AIBRAND_FILE ) . '/includes/wpseo/author.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/wpseo/class-yoast-redirects.php';
		require_once dirname( AIBRAND_FILE ) . '/includes/wpseo/disable-frontend-inspector.php';
	}

}
