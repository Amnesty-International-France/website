<?php

declare( strict_types = 1 );

namespace Amnesty;

use WP_Term;

new Taxonomy_Keywords();

/**
 * Register the Resource Types taxonomy
 *
 * @package Amnesty\Taxonomies
 */
class Taxonomy_Keywords extends Taxonomy {

	/**
	 * Taxonomy source slug
	 *
	 * @var string
	 */
	protected $name = 'keyword';

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	protected $slug = 'keyword';

	/**
	 * Object type(s) to register the taxonomy for
	 *
	 * @var array
	 */
	protected $object_types = [ 'page', 'post', 'tribe_events', 'petition'];

	/**
	 * Taxonomy registration arguments
	 *
	 * @var array
	 */
	protected $args = [
		'hierarchical' => true,
		'rewrite'               => ['slug' => 'mot-cle'],
		'show_admin_column'     => true,
		'show_in_rest'          => true,
		'query_var'             => true,
		'rest_base'             => 'keyword',
		'update_count_callback' => '_update_generic_term_count',
	];

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		parent::__construct();

		if ( ! $this->is_enabled() ) {
			return;
		}

		// has to be run late to ensure the taxonomy is registered
		add_action( 'init', [ $this, 'redirect' ] );
	}

	/**
	 * Redirect old term URIs to search
	 *
	 * @return void
	 */
	public function redirect(): void {
		// just in case
		if ( headers_sent() || is_admin() ) {
			return;
		}

		$path  = wp_parse_url( current_url(), PHP_URL_PATH ) ?: '/';
		$regex = sprintf( '#^/[a-z]{2}/%s/([a-z-]+)/$#', preg_quote( $this->slug, '#' ) );

		// not an old topic url
		if ( 1 !== preg_match( $regex, $path, $match ) || ! isset( $match[1] ) ) {
			return;
		}

		$term = get_term_by( 'slug', $match[1], $this->slug );

		// can't find the term
		if ( ! is_a( $term, WP_Term::class ) ) {
			return;
		}

		// redirect to filtered search
		wp_safe_redirect( add_query_arg( [ "q{$this->slug}" => $term->term_id ], amnesty_search_url() ) );
		die;
	}

	/**
	 * Register taxonomy slug setting for localisation
	 *
	 * @return void
	 */
	public function add_settings() {
		// no-op
	}

	/**
	 * Save the localised taxonomy slug
	 *
	 * @param array $data $_POST data
	 *
	 * @return void
	 */
	public function save_settings( array $data = [] ) {
		// no-op
	}

	/**
	 * Declare the taxonomy labels
	 *
	 * @param bool $defaults whether to return default labels or not
	 *
	 * @return object
	 */
	public static function labels( bool $defaults = false ): object {
		$default_labels = [
			/* translators: [admin] */
			'name'                  => _x( 'Mots clés', 'taxonomy general name', 'amnesty' ),
			/* translators: [admin] */
			'singular_name'         => _x( 'Mot clé', 'taxonomy singular name', 'amnesty' ),
			/* translators: [admin] */
			'search_items'          => __( 'Recherche par Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'all_items'             => __( 'Tout les Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'parent_item'           => __( 'Mot clé parent', 'amnesty' ),
			/* translators: [admin] */
			'parent_item_colon'     => __( 'Mot clé parent:', 'amnesty' ),
			/* translators: [admin] */
			'edit_item'             => __( 'Editer un Mot clé', 'amnesty' ),
			/* translators: [admin] */
			'view_item'             => __( 'Voir le Mot clé', 'amnesty' ),
			/* translators: [admin] */
			'update_item'           => __( 'Mettre à jour le Mot clé', 'amnesty' ),
			/* translators: [admin] */
			'add_new_item'          => __( 'Ajouter un nouveau Mot clé', 'amnesty' ),
			/* translators: [admin] */
			'new_item_name'         => __( 'Nouveau Mot clé', 'amnesty' ),
			/* translators: [admin] */
			'add_or_remove_items'   => __( 'Ajouter ou enlever des Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'choose_from_most_used' => __( 'Choisir parmi les Mots clés les plus fréquents', 'amnesty' ),
			/* translators: [admin] */
			'not_found'             => __( 'Aucun Mot clé trouvé.', 'amnesty' ),
			/* translators: [admin] */
			'no_terms'              => __( 'Aucun Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'items_list_navigation' => __( 'Navigation dans la liste des Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'items_list'            => __( 'Liste des Mots clés', 'amnesty' ),
			/* translators: [admin] Tab heading when selecting from the most used terms. */
			'most_used'             => _x( 'Most Used', 'Mots clés', 'amnesty' ),
			/* translators: [admin] */
			'back_to_items'         => __( '&larr; Retour vers les Mots clés', 'amnesty' ),
		];

		if ( $defaults ) {
			return (object) $default_labels;
		}

		$options = get_option( 'amnesty_localisation_options_page' );

		if ( ! isset( $options['keyword_labels'][0] ) ) {
			return (object) $default_labels;
		}

		$config_labels = [];

		foreach ( $options['keyword_labels'][0] as $key => $value ) {
			$key = str_replace( 'keyword_label_', '', $key );

			$config_labels[ $key ] = $value;
		}

		return (object) array_merge( $default_labels, $config_labels );
	}

}
