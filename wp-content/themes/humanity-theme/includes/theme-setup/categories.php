<?php

if( !function_exists( 'setup_categories' ) ) {
	function setup_categories() {
		$categories_defaut = [
			'Actualités' => [
				'slug' => 'actualites'
			],
			'Dossiers' => [
				'slug' => 'dossiers'
			],
			'Campagnes' => [
				'slug' => 'campagnes'
			],
			'Articles Chronique' => [
				'slug' => 'chroniques'
			],
			'Enquêtes' => [
				'slug' => 'enquetes',
				'parent' => 'actualites'
			],
			'Entretiens' => [
				'slug' => 'entretiens',
				'parent' => 'actualites'
			],
			'Portraits' => [
				'slug' => 'portraits',
				'parent' => 'actualites'
			],
			'Rapports' => [
				'slug' => 'rapports',
				'parent' => 'actualites'
			],
			'Témoignages' => [
				'slug' => 'temoignages',
				'parent' => 'actualites'
			],
			'Tribunes' => [
				'slug' => 'tribunes',
				'parent' => 'actualites'
			]
		];

		foreach ($categories_defaut as $nom => $details) {
			if (!term_exists($nom, 'category')) {
				$args = ['slug' => $details['slug']];
				if( isset($details['parent']) ) {
					$args['parent'] = get_term_by( 'slug', $details['parent'], 'category')->term_id;
				}
				wp_insert_term(
					$nom,
					'category',
					$args
				);
			}
		}
	}

	add_action( 'init', 'setup_categories' );
}
