<?php

declare(strict_types=1);

add_action('init', function () {
    register_post_type('chronique', [
        'labels' => [
            'name' => 'Chroniques',
            'singular_name' => 'Chronique',
            'menu_name' => 'Chroniques',
            'all_items' => 'Toutes les Chroniques',
            'edit_item' => 'Modifier la Chronique',
            'view_item' => 'Voir la Chronique',
            'view_items' => 'Voir Chroniques',
            'add_new_item' => 'Ajouter une Chronique',
            'add_new' => 'Ajouter une Chronique',
            'new_item' => 'Nouvelle Chronique',
            'parent_item_colon' => 'Chronique parent :',
            'search_items' => 'Rechercher Chroniques',
            'not_found' => 'Aucune chroniques trouvé',
            'not_found_in_trash' => 'Aucune chronique trouvée dans la corbeille',
            'archives' => 'Archives des Chronique',
            'attributes' => 'Attributs des Chronique',
            'insert_into_item' => 'Insérer dans chronique',
            'uploaded_to_this_item' => 'Téléversé sur cette chronique',
            'filter_items_list' => 'Filtrer la liste des chroniques',
            'filter_by_date' => 'Filtrer les chroniques par date',
            'items_list_navigation' => 'Navigation dans la liste des Chroniques',
            'items_list' => 'Liste Chroniques',
            'item_published' => 'Chronique publié.',
            'item_published_privately' => 'Chronique publié en privé.',
            'item_reverted_to_draft' => 'Chronique repassé en brouillon.',
            'item_scheduled' => 'Chronique planifié.',
            'item_updated' => 'Chronique mis à jour.',
            'item_link' => 'Lien Chronique',
            'item_link_description' => 'Un lien vers une chronique.',
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-admin-page',
        'menu_position' => 21,
        'supports' => [
            0 => 'title',
            1 => 'editor',
            2 => 'custom-fields',
        ],
        'delete_with_user' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'has_archive' => false,
        'rewrite' => [
            'slug' => 'chronique/archives',
            'with_front' => false,
        ],
    ]);
});

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_68a440dd84b6a',
        'title' => 'Sommaire d\'une Chronique',
        'fields' => [
            [
                'key' => 'field_68a440e3940bf',
                'label' => 'Titre sommaire',
                'name' => 'summary_title',
                'aria-label' => '',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'maxlength' => '',
                'allow_in_bindings' => 0,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ],
            [
                'key' => 'field_68a440dd_mois_select',
                'label' => 'Mois de publication',
                'name' => 'publication_month',
                'type' => 'select',
                'instructions' => 'Sélectionnez le mois de publication.',
                'required' => 1,
                'choices' => [
                    '01' => 'Janvier',
                    '02' => 'Février',
                    '03' => 'Mars',
                    '04' => 'Avril',
                    '05' => 'Mai',
                    '06' => 'Juin',
                    '07' => 'Juillet',
                    '08' => 'Août',
                    '09' => 'Septembre',
                    '10' => 'Octobre',
                    '11' => 'Novembre',
                    '12' => 'Décembre',
                ],
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_68a440dd_annee_select',
                'label' => 'Année de publication',
                'name' => 'publication_year',
                'type' => 'select',
                'instructions' => 'Sélectionnez l\'année de publication.',
                'required' => 1,
                'choices' => (function () {
                    $years = [];
                    $current_year = date('Y');
                    for ($i = $current_year + 1; $i >= $current_year - 25; $i--) {
                        $years[$i] = $i;
                    }
                    return $years;
                })(),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'wrapper' => [
                    'width' => '50',
                ],
            ],
            [
                'key' => 'field_68a44209940c1',
                'label' => 'Image de couverture',
                'name' => 'cover_image',
                'aria-label' => '',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'array',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
                'allow_in_bindings' => 0,
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_68a442b0940c2',
                'label' => 'Image de la couverture avec le magazine ouvert',
                'name' => 'cover_image_with_magazine_open',
                'aria-label' => '',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'return_format' => 'array',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
                'allow_in_bindings' => 0,
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_68a44323940c4',
                'label' => 'Articles liés (Chronique)',
                'name' => '_related_posts_selected',
                'aria-label' => '',
                'type' => 'relationship',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'post_type' => [
                    0 => 'post',
                ],
                'post_status' => 'publish',
                'taxonomy' => [
                    0 => 'category:chroniques',
                ],
                'filters' => [
                    0 => 'search',
                    1 => 'post_type',
                ],
                'return_format' => 'object',
                'min' => '',
                'max' => '',
                'allow_in_bindings' => 0,
                'elements' => '',
                'bidirectional' => 0,
                'bidirectional_target' => [],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'chronique',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ]);
});

/**
 * Fix breadcrumbs for a single chronicle
 *
 * @param array $links
 *
 * @return array
 */
function amnesty_add_chronicle_parent_to_breadcrumb($links): array
{
    if (is_singular('chronique')) {
        $chronicle_parent_page = get_page_by_path('chronique');

        if ($chronicle_parent_page) {
            $breadcrumb_parent = [
                'url'  => get_permalink($chronicle_parent_page->ID),
                'text' => get_the_title($chronicle_parent_page->ID),
            ];

            array_splice($links, 1, 0, [ $breadcrumb_parent ]);
        }
    }

    return $links;
}
add_filter('wpseo_breadcrumb_links', 'amnesty_add_chronicle_parent_to_breadcrumb');

function amnesty_custom_chronicle_permalink($post_link, $post)
{
    if ('chronique' !== $post->post_type || !function_exists('amnesty_get_chronicle_structure_info')) {
        return $post_link;
    }

    $chronicle_info = amnesty_get_chronicle_structure_info();
    $archive_url = !empty($chronicle_info) ? $chronicle_info['archives_url'] : false;

    if ($archive_url) {
        $base = trailingslashit($archive_url);
        $post_link = $base . $post->post_name . '/';
    }

    return $post_link;
}

add_filter('post_type_link', 'amnesty_custom_chronicle_permalink', 10, 2);

/**
 * Fix the query on the "Chronicle" page so that it correctly finds the latest column.
 */
function amnesty_fix_chronicle_promo_page_query($query): void
{
    if (!function_exists('amnesty_get_chronicle_structure_info') || !$query->is_main_query()) {
        return;
    }

    $chronicle_info = amnesty_get_chronicle_structure_info();
    $promo_page_id = $chronicle_info ? $chronicle_info['promo_page_id'] : null;

    if ($promo_page_id && (int) $query->get('page_id') === $promo_page_id) {
        $query->set('post_type', 'page');
    }
}

if (!is_admin()) {
    add_action('pre_get_posts', 'amnesty_fix_chronicle_promo_page_query');
}

function amnesty_custom_chronique_breadcrumbs($links)
{
    if (!is_singular('chronique')) {
        return $links;
    }

    if (!function_exists('amnesty_get_chronicle_structure_info')) {
        return $links;
    }

    $info = amnesty_get_chronicle_structure_info();
    if (!$info) {
        return $links;
    }

    $promo_page_id = $info['promo_page_id'];
    $archives_url = $info['archives_url'];

    $new_breadcrumb_trail = [];

    $ancestors = get_post_ancestors($promo_page_id);
    if (!empty($ancestors)) {
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor_id) {
            $new_breadcrumb_trail[] = [
                'url' => get_permalink($ancestor_id),
                'text' => get_the_title($ancestor_id),
            ];
        }
    }

    $new_breadcrumb_trail[] = [
        'url' => get_permalink($promo_page_id),
        'text' => 'Magazine la chronique',
    ];

    $new_breadcrumb_trail[] = [
        'url' => $archives_url,
        'text' => 'Archives',
    ];

    array_splice($links, 1, 0, $new_breadcrumb_trail);

    return $links;
}
add_filter('wpseo_breadcrumb_links', 'amnesty_custom_chronique_breadcrumbs');
