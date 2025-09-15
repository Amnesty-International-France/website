<?php

if (!function_exists('setup_document_instance_types')) {
    function setup_document_instance_types()
    {
        $categories_default = [
            'Assemblée générale' => 'assemblee-generale',
            'Conseil d’administration' => 'conseil-dadministration',
            'Comité des candidatures' => 'comite-des-candidatures',
            'Conseil des finances et des risques financiers' => 'conseil-des-finances-et-des-risques-financiers',
            'Représentants des jeunes' => 'representant-des-jeunes',
            'Conseil national' => 'conseil-national',
        ];

        foreach ($categories_default as $name => $slug) {
            if (!term_exists($slug, 'document_instance_type')) {
                wp_insert_term($name, 'document_instance_type', ['slug' => $slug]);
            }
        }
    }

    add_action('after_switch_theme', 'setup_document_instance_types');
}
