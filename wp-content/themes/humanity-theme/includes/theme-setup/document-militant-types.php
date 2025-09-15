<?php

if (!function_exists('setup_document_militant_types')) {
    function setup_document_militant_types()
    {
        $categories_default = [
            'Fiche pratique' => 'fiche-pratique',
            'Administratif' => 'administratif',
            'Livret d’accompagnement' => 'livret-daccompagnement',
            'Modèles' => 'modeles',
            'Guide militant' => 'guide-militant',
            'Matériel militant' => 'materiel-militant',
            'Visuels' => 'visuels',
        ];

        foreach ($categories_default as $name => $slug) {
            if (!term_exists($slug, 'document_militant_type')) {
                wp_insert_term($name, 'document_militant_type', ['slug' => $slug]);
            }
        }
    }

    add_action('after_switch_theme', 'setup_document_militant_types');
}
