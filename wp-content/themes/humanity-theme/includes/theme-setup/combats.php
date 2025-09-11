<?php

if (!function_exists('setup_combats')) {
    function setup_combats()
    {
        $categories_defaut = [
            'Liberté d\'expression' => [
                'slug' => 'liberte-d-expression'
            ],
            'Droit de manifester' => [
                'slug' => 'droit-de-manifester'
            ],
            'Technologies et droits humains' => [
                'slug' => 'technologies-et-droits-humains'
            ],
            'Justice raciale' => [
                'slug' => 'justice-raciale'
            ],
            'Justice de genre' => [
                'slug' => 'justice-de-genre'
            ],
            'Réfugiés et migrants' => [
                'slug' => 'refugies-et-migrants'
            ],
            'Justice climatique' => [
                'slug' => 'justice-climatique'
            ],
            'Droits économiques et sociaux' => [
                'slug' => 'droits-economiques-et-sociaux'
            ],
            'Respect du droit international humanitaire' => [
                'slug' => 'respect-du-droit-international-humanitaire'
            ],
            'Justice internationale' => [
                'slug' => 'justice-internationale'
            ],
            'Peine de mort' => [
                'slug' => 'peine-de-mort'
            ],
        ];

        foreach ($categories_defaut as $nom => $details) {
            if (!term_exists($details['slug'], 'combat')) {
                $args = ['slug' => $details['slug']];
                if (isset($details['parent'])) {
                    $args['parent'] = get_term_by('slug', $details['parent'], 'combat')->term_id;
                }
                wp_insert_term(
                    $nom,
                    'combat',
                    $args
                );
            }
        }
    }

    add_action('after_switch_theme', 'setup_combats');
}
