<?php

if( !function_exists( 'setup_categories' ) ) {
	function setup_categories() {
		$categories_defaut = [
			'Actualité' => [
				'slug' => 'actualites'
			],
			'Dossier' => [
				'slug' => 'dossiers'
			],
			'Campagne' => [
				'slug' => 'campagnes'
			],
			'Article Chronique' => [
				'slug' => 'chroniques'
			]
		];

		foreach ($categories_defaut as $nom => $details) {
			if (!term_exists($nom, 'category')) {
				wp_insert_term(
					$nom,
					'category',
					array('slug' => $details['slug'])
				);
			}
		}
	}

	add_action( 'init', 'setup_categories' );
}
