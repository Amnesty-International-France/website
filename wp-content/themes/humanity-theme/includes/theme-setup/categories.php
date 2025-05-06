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
