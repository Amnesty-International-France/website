<?php

if( !function_exists( 'setup_combats' ) ) {
	function setup_combats() {
		$categories_defaut = [
			'Liberté de réunion pacifique' => [
				'slug' => 'liberte-de-reunion-pacifique'
			],
			'Violences policières' => [
				'slug' => 'violences-policieres',
				'parent' => 'liberte-de-reunion-pacifique'
			],
			'Liberté d\'expression' => [
				'slug' => 'liberte-dexpression'
			],
			'Défenseurs des droits humains ' => [
				'slug' => 'defenseurs-des-droits-humains',
				'parent' => 'liberte-dexpression'
			],
			'Liberté d\'association' => [
				'slug' => 'liberte-dassociation'
			],
			'Technologies et droits humains' => [
				'slug' => 'technologies-et-droits-humains'
			],
			'Reconnaissance faciale' => [
				'slug' => 'reconnaissance-faciale',
				'parent' => 'technologies-et-droits-humains'
			],
			'Algorithmes et réseaux sociaux' => [
				'slug' => 'algorithmes-et-reseaux-sociaux',
				'parent' => 'technologies-et-droits-humains'
			],
			'Surveillance ' => [
				'slug' => 'surveillance',
				'parent' => 'technologies-et-droits-humains'
			],
			'Justice raciale' => [
				'slug' => 'justice-raciale'
			],
			'Antisémitisme' => [
				'slug' => 'antisemitisme',
				'parent' => 'justice-raciale'
			],
			'Voile' => [
				'slug' => 'voile',
				'parent' => 'justice-raciale'
			],
			'Contrôle au faciès' => [
				'slug' => 'controle-au-facies',
				'parent' => 'justice-raciale'
			],
			'Justice de genre' => [
				'slug' => 'justice-de-genre'
			],
			'Droits des femmes' => [
				'slug' => 'droits-des-femmes',
				'parent' => 'justice-de-genre'
			],
			'Droit à l\'avortement' => [
				'slug' => 'droit-a-lavortement',
				'parent' => 'justice-de-genre'
			],
			'Violences sexistes et sexuelles' => [
				'slug' => 'violences-sexistes-et-sexuelles',
				'parent' => 'justice-de-genre'
			],
			'Droits des LGBTI+' => [
				'slug' => 'droits-des-lgbti',
				'parent' => 'justice-de-genre'
			],
			'Réfugiés et migrants' => [
				'slug' => 'refugies-et-migrants'
			],
			'Justice climatique' => [
				'slug' => 'justice-climatique'
			],
			'Responsabilité sociale des entreprises' => [
				'slug' => 'responsabilite-sociale-des-entreprises',
				'parent' => 'justice-climatique'
			],
			'Droits économiques et sociaux' => [
				'slug' => 'droits-economiques-et-sociaux'
			],
			'Droit au logement' => [
				'slug' => 'droit-au-logement',
				'parent' => 'droits-economiques-et-sociaux'
			],
			'Respect du droit international humanitaire' => [
				'slug' => 'respect-du-droit-international-humanitaire'
			],
			'Contrôle des armes' => [
				'slug' => 'controle-des-armes',
				'parent' => 'respect-du-droit-international-humanitaire'
			],
			'Conflits armés et populations' => [
				'slug' => 'conflits-armes-et-populations',
				'parent' => 'respect-du-droit-international-humanitaire'
			],
			'Crime de guerre ' => [
				'slug' => 'crime-de-guerre',
				'parent' => 'respect-du-droit-international-humanitaire'
			],
			'Crime contre l\'humanité' => [
				'slug' => 'crime-contre-lhumanite',
				'parent' => 'respect-du-droit-international-humanitaire'
			],
			'Génocide' => [
				'slug' => 'genocide',
				'parent' => 'respect-du-droit-international-humanitaire'
			],
			'Lutte contre l\'impunité' => [
				'slug' => 'lutte-contre-limpunite'
			],
			'Compétence universelle' => [
				'slug' => 'competence-universelle',
				'parent' => 'lutte-contre-limpunite'
			],
			'Peine de mort' => [
				'slug' => 'peine-de-mort'
			],
			'Sous-combats à part' => [
				'slug' => 'sous-combats-a-part'
			],
			'Droits de l\'enfant' => [
				'slug' => 'droits-de-lenfant',
				'parent' => 'sous-combats-a-part'
			]
		];

		foreach ($categories_defaut as $nom => $details) {
			if (!term_exists($nom, 'combat')) {
				$args = ['slug' => $details['slug']];
				if( isset($details['parent']) ) {
					$args['parent'] = get_term_by( 'slug', $details['parent'], 'combat')->term_id;
				}
				wp_insert_term(
					$nom,
					'combat',
					$args
				);
			}
		}
	}

	add_action( 'init', 'setup_combats' );
}
