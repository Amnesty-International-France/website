<?php


if (!function_exists('setup_keywords')) {
	function setup_keywords()
	{
		$keywords_default = [
			'Rapport annuel' => [
				'slug' => 'rapport-annuel'
			],
			'Conflit israélo-palestinien' => [
				'slug' => 'conflit-israelo-palestinien'
			],
			'Conflit ukrainien' => [
				'slug' => 'conflit-ukrainien'
			],
			'Changez leur histoire' => [
				'slug' => 'changez-leur-histoire'
			],
			'Manifestez-vous' => [
				'slug' => 'manifestez-vous'
			],
			'Femmes afghanes' => [
				'slug' => 'femmes-afghanes'
			],
			'Apartheid' => [
				'slug' => 'apartheid'
			],
			'Transgenre' => [
				'slug' => 'transgenre'
			],
			'Délit de solidarité' => [
				'slug' => 'delit-de-solidarite'
			],
			'Elections' => [
				'slug' => 'elections'
			],
			'Education aux droits humains' => [
				'slug' => 'education-aux-droits-humains'
			],
			'Formation' => [
				'slug' => 'formation'
			],
			'Agir' => [
				'slug' => 'agir'
			],
			'Militantisme' => [
				'slug' => 'militantisme'
			],
		];

		foreach ($keywords_default as $nom => $details) {
			if (!term_exists($details['slug'], 'keyword')) {
				wp_insert_term(
					$nom,
					'keyword',
					array('slug' => $details['slug'])
				);
			}
		}
	}

	add_action('after_switch_theme', 'setup_keywords');
}
