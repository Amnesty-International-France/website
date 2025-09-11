<?php

if (!function_exists('setup_document_types')) {
    function setup_document_types()
    {
        $categories_default = [
            'Rapport' => 'rapport',
            'Document' => 'document',
            "Kit d'activisme" => 'kit-activisme',
            'Fiche pédagogique' => 'fiche-pedagogique',
        ];

        foreach ($categories_default as $name => $slug) {
            if (!term_exists($slug, 'document_type')) {
                wp_insert_term($name, 'document_type', ['slug' => $slug]);
            }
        }
    }

    add_action('after_switch_theme', 'setup_document_types');
}
