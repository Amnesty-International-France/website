<?php

declare( strict_types = 1 );

namespace Amnesty\Branding;

/**
 * Load custom WP templates
 *
 * @see https://github.com/amnestywebsite/amnesty-wp-theme/issues/2805
 */
class Template_Loader {

	/**
	 * Store for query vars for iteration
	 *
	 * @var array<int,string>
	 */
	protected static array $vars = [
		'ai_class',
		'ai_subclass',
		'ai_index',
		'ai_year',
		'ai_doc_lang',
	];

	/**
	 * Bind hooks
	 */
	public function __construct() {
		add_filter( 'template_include', [ $this, 'template_include' ], 11 );
	}

	/**
	 * Override default template for templates provided by this plugin
	 *
	 * @param string $template the original template
	 *
	 * @return string
	 */
	public function template_include( string $template = '' ): string {
		$template = $this->maybe_load_petition_template( $template );

		return $template;
	}

	/**
	 * Override the template when on some form of petitions view
	 *
	 * @param string $template the current template
	 *
	 * @return string
	 */
	protected function maybe_load_petition_template( string $template = '' ): string {
		$petition_slug = get_option( 'aip_petition_slug' ) ?: 'petition';

		if ( is_singular( $petition_slug ) ) {
			return dirname( AIBRAND_FILE ) . '/views/templates/petitions/single.php';
		}

		if ( is_post_type_archive( $petition_slug ) ) {
			return dirname( AIBRAND_FILE ) . '/views/templates/petitions/archive.php';
		}

		return $template;
	}

}
