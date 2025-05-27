<?php

function amnesty_filter_cpt_by_multiple_taxonomies( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( is_post_type_archive( ['landmark', 'fiche_pays'] ) ) {

		$tax_query = [];

		$filterable_taxonomies = [ 'landmark_category', 'combat', 'location', 'type', 'theme' ];

		foreach ( $filterable_taxonomies as $taxonomy ) {
			$param_name = 'q' . $taxonomy;

			if ( isset( $_GET[ $param_name ] ) ) {
				$terms = array_map( 'intval', (array) $_GET[ $param_name ] );

				if ( ! empty( $terms ) ) {
					$tax_query[] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms,
					];
				}
			}
		}

		if ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}
	}
}

add_action( 'pre_get_posts', 'amnesty_filter_cpt_by_multiple_taxonomies' );

