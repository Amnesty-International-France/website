<?php

/**
 * Riposte victory post type.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}


/**
 * Register Riposte theme taxonomy.
 *
 * @return void
 */
function aif_riposte_register_theme_taxonomy(): void
{
	register_taxonomy(
		'riposte_theme',
		[ 'riposte_victory' ],
		[
			'labels'            => [
				'name'                       => __('Thématiques', 'aif-riposte'),
				'singular_name'              => __('Thématique', 'aif-riposte'),
				'menu_name'                  => __('Thématiques', 'aif-riposte'),
				'all_items'                  => __('Toutes les thématiques', 'aif-riposte'),
				'edit_item'                  => __('Modifier la thématique', 'aif-riposte'),
				'view_item'                  => __('Voir la thématique', 'aif-riposte'),
				'update_item'                => __('Mettre à jour la thématique', 'aif-riposte'),
				'add_new_item'               => __('Ajouter une thématique', 'aif-riposte'),
				'new_item_name'              => __('Nom de la nouvelle thématique', 'aif-riposte'),
				'search_items'               => __('Rechercher une thématique', 'aif-riposte'),
				'popular_items'              => __('Thématiques populaires', 'aif-riposte'),
				'separate_items_with_commas' => __('Séparer les thématiques par des virgules', 'aif-riposte'),
				'add_or_remove_items'        => __('Ajouter ou retirer des thématiques', 'aif-riposte'),
				'choose_from_most_used'      => __('Choisir parmi les thématiques les plus utilisées', 'aif-riposte'),
				'not_found'                  => __('Aucune thématique trouvée', 'aif-riposte'),
			],
			'public'            => false,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => false,
		]
	);
}
add_action('init', 'aif_riposte_register_theme_taxonomy', 5);


/**
 * Register Riposte tag taxonomy.
 *
 * @return void
 */
function aif_riposte_register_tag_taxonomy(): void
{
	register_taxonomy(
		'riposte_tag',
		[ 'riposte_victory' ],
		[
			'labels' => [
				'name'                       => 'Mots clés',
				'singular_name'              => 'Mot clé',
				'search_items'               => 'Rechercher des mots clés',
				'popular_items'              => 'Mots clés populaires',
				'all_items'                  => 'Tous les mots clés',
				'edit_item'                  => 'Modifier le mot clé',
				'update_item'                => 'Mettre à jour le mot clé',
				'add_new_item'               => 'Ajouter un mot clé',
				'new_item_name'              => 'Nouveau mot clé',
				'separate_items_with_commas' => 'Séparez les mots clés par des virgules',
				'add_or_remove_items'        => 'Ajouter ou retirer des mots clés',
				'choose_from_most_used'      => 'Choisir parmi les mots clés les plus utilisés',
				'not_found'                  => 'Aucun mot clé trouvé',
				'menu_name'                  => 'Mots clés',
			],
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'rewrite'           => false,
		]
	);
}
add_action('init', 'aif_riposte_register_tag_taxonomy', 5);



/**
 * Register Riposte Victory CPT.
 *
 * @return void
 */
function aif_riposte_register_post_type(): void
{
	register_post_type(
		'riposte_victory',
		[
			'labels'              => [
				'name'                  => __('Ripostes', 'aif-riposte'),
				'singular_name'         => __('Riposte', 'aif-riposte'),
				'menu_name'             => __('Ripostes', 'aif-riposte'),
				'name_admin_bar'        => __('Riposte', 'aif-riposte'),
				'add_new'               => __('Ajouter', 'aif-riposte'),
				'add_new_item'          => __('Ajouter une riposte', 'aif-riposte'),
				'edit_item'             => __('Modifier la riposte', 'aif-riposte'),
				'new_item'              => __('Nouvelle riposte', 'aif-riposte'),
				'view_item'             => __('Voir la riposte', 'aif-riposte'),
				'view_items'            => __('Voir les ripostes', 'aif-riposte'),
				'search_items'          => __('Rechercher une riposte', 'aif-riposte'),
				'not_found'             => __('Aucune riposte trouvée', 'aif-riposte'),
				'not_found_in_trash'    => __('Aucune riposte dans la corbeille', 'aif-riposte'),
				'all_items'             => __('Toutes les ripostes', 'aif-riposte'),
				'archives'              => __('Archives des ripostes', 'aif-riposte'),
				'attributes'            => __('Attributs de la riposte', 'aif-riposte'),
				'insert_into_item'      => __('Insérer dans la riposte', 'aif-riposte'),
				'uploaded_to_this_item' => __('Téléversé sur cette riposte', 'aif-riposte'),
				'filter_items_list'     => __('Filtrer les ripostes', 'aif-riposte'),
				'items_list_navigation' => __('Navigation de la liste des ripostes', 'aif-riposte'),
				'items_list'            => __('Liste des ripostes', 'aif-riposte'),
			],
			'description'         => __('Ripostes affichées sur une archive filtrable.', 'aif-riposte'),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => [
				'slug'       => 'ripostes',
				'with_front' => true,
			],
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
				'page-attributes',
				'custom-fields',
			],
			'taxonomies'          => [
				'location',
				'riposte_theme',
				'riposte_tag',
			],
			'menu_icon'           => 'dashicons-hammer',
			'show_ui'             => true,
			'show_in_rest'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		]
	);

}
add_action('init', 'aif_riposte_register_post_type');

/**
 * Attach taxonomies to Riposte Victory CPT.
 *
 * @return void
 */
function aif_riposte_register_taxonomies_for_post_type(): void
{
	register_taxonomy_for_object_type('location', 'riposte_victory');
	register_taxonomy_for_object_type('riposte_theme', 'riposte_victory');
	register_taxonomy_for_object_type('riposte_tag', 'riposte_victory');
}
add_action('init', 'aif_riposte_register_taxonomies_for_post_type', 20);


