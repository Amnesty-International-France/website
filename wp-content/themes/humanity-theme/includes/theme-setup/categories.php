<?php

if (!function_exists('setup_categories')) {
    function setup_categories()
    {
        $categories_defaut = [
            'Actualités' => [
                'singular_name' => 'Actualité',
                'slug' => 'actualites'
            ],
            'Dossiers' => [
                'singular_name' => 'Dossier',
                'slug' => 'dossiers'
            ],
            'Campagnes' => [
                'singular_name' => 'Campagne',
                'slug' => 'campagnes'
            ],
            'Articles La Chronique' => [
                'singular_name' => 'Article La Chronique',
                'slug' => 'chroniques'
            ],
        ];

        foreach ($categories_defaut as $nom => $details) {
            if (!term_exists($details['slug'], 'category')) {
                $term = wp_insert_term(
                    $nom,
                    'category',
                    ['slug' => $details['slug']]
                );
                add_term_meta($term['term_id'], '_category_singular_name', 'field_682c408d2e95b');
                add_term_meta($term['term_id'], 'category_singular_name', $details['singular_name']);
            }
        }
    }

    add_action('after_switch_theme', 'setup_categories');
}
