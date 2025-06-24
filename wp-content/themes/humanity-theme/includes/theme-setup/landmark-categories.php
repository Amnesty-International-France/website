<?php

if (!function_exists('setup_landmark_categories')) {
	function setup_landmark_categories()
	{
		$categories_default = [
			'Droit international' => 'droit-international',
			'Décryptage' => 'decryptage',
			'Désintox' => 'desintox',
			'Data' => 'data',
		];

		foreach ($categories_default as $name => $slug) {
			if (!term_exists($slug, 'landmark_category')) {
				wp_insert_term($name, 'landmark_category', ['slug' => $slug]);
			}
		}
	}

	add_action('init', 'setup_landmark_categories');
}
