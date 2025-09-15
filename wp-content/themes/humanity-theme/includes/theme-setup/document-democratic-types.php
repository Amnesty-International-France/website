<?php

if (!function_exists('setup_document_democratic_types')) {
    function setup_document_democratic_types()
    {
        $categories_default = [
            'Documents préparatoires' => 'documents-preparatoires',
            'Candidatures' => 'candidatures',
            'Formulaires' => 'formulaires',
            'Procès verbaux' => 'proces-verbaux',
            'Comptes-rendus' => 'comptes-rendus',
            'Délibérations' => 'deliberations',
            'Chartes et protocoles' => 'chartes-et-protocoles',
            'Politiques et stratégues' => 'politiques-et-strategies',
            'Autres' => 'autres',
        ];

        foreach ($categories_default as $name => $slug) {
            if (!term_exists($slug, 'document_democratic_type')) {
                wp_insert_term($name, 'document_democratic_type', ['slug' => $slug]);
            }
        }
    }

    add_action('after_switch_theme', 'setup_document_democratic_types');
}
