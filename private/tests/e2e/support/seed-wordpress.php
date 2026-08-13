<?php

/**
 * Insert a published post without triggering save_post or ACF hooks.
 *
 * Some theme hooks make real Salesforce calls, so wp_insert_post() and
 * `wp post create` must not be used by this E2E seed.
 */
function aif_e2e_insert_post(array $overrides): int
{
    global $wpdb;

    $now = current_time('mysql');
    $now_gmt = current_time('mysql', true);
    $defaults = [
        'post_author' => 1,
        'post_date' => $now,
        'post_date_gmt' => $now_gmt,
        'post_content' => '',
        'post_excerpt' => '',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_password' => '',
        'to_ping' => '',
        'pinged' => '',
        'post_modified' => $now,
        'post_modified_gmt' => $now_gmt,
        'post_content_filtered' => '',
        'post_parent' => 0,
        'menu_order' => 0,
        'post_type' => 'page',
        'post_mime_type' => '',
        'comment_count' => 0,
    ];

    $wpdb->insert($wpdb->posts, array_merge($defaults, $overrides));

    $post_id = (int) $wpdb->insert_id;
    clean_post_cache($post_id);

    return $post_id;
}

if (get_option('permalink_structure') !== '/%postname%/') {
    update_option('permalink_structure', '/%postname%/');
}

if (!get_page_by_path('mot-de-passe-oublie')) {
    aif_e2e_insert_post([
        'post_title' => 'Mot de passe oublié',
        'post_name' => 'mot-de-passe-oublie',
        'guid' => home_url('/mot-de-passe-oublie/'),
    ]);
}

if (!get_page_by_path('newsletter')) {
    aif_e2e_insert_post([
        'post_title' => 'Newsletter',
        'post_name' => 'newsletter',
        'post_content' => '<!-- wp:pattern {"slug":"amnesty/page-nl-content"} /-->',
        'guid' => home_url('/newsletter/'),
    ]);
}

$front_page = get_page_by_path('accueil-e2e', OBJECT, 'page');
if (!$front_page) {
    $front_page_id = aif_e2e_insert_post([
        'post_title' => 'Accueil (e2e)',
        'post_name' => 'accueil-e2e',
        'post_content' => '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Bienvenue chez Amnesty International France</h1><!-- /wp:heading -->',
        'guid' => home_url('/accueil-e2e/'),
    ]);
} else {
    $front_page_id = (int) $front_page->ID;
}

update_option('show_on_front', 'page');
update_option('page_on_front', $front_page_id);

if (!get_page_by_path('aif-e2e-petition', OBJECT, 'petition')) {
    $petition_id = aif_e2e_insert_post([
        'post_title' => 'Justice pour toustes (e2e)',
        'post_excerpt' => 'Nous demandons la libération immédiate des militant·e·s emprisonné·e·s injustement.',
        'post_name' => 'aif-e2e-petition',
        'post_type' => 'petition',
        'guid' => home_url('/petitions/aif-e2e-petition/'),
    ]);

    update_post_meta($petition_id, 'type', 'petition');
    update_post_meta($petition_id, 'date_de_fin', gmdate('Y-m-d', strtotime('+1 year')));
    update_post_meta($petition_id, 'objectif_signatures', 1000);
}

delete_transient('amnesty_fiche_pays_list');
if (!get_page_by_path('france-e2e', OBJECT, 'fiche_pays')) {
    aif_e2e_insert_post([
        'post_title' => 'France',
        'post_name' => 'france-e2e',
        'post_type' => 'fiche_pays',
        'guid' => home_url('/pays/france-e2e/'),
    ]);
}

$menu_name = 'Main Menu (e2e)';
$menu = wp_get_nav_menu_object($menu_name);
if (!$menu) {
    $menu_id = wp_create_nav_menu($menu_name);

    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => 'Accueil',
        'menu-item-url' => home_url('/accueil-e2e/'),
        'menu-item-status' => 'publish',
    ]);
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => 'Nos pétitions',
        'menu-item-url' => home_url('/petitions/'),
        'menu-item-status' => 'publish',
    ]);
} else {
    $menu_id = (int) $menu->term_id;
}

$locations = get_theme_mod('nav_menu_locations', []);
$locations['main-menu'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

if (!get_page_by_path('don')) {
    $donation_page_id = aif_e2e_insert_post([
        'post_title' => 'Faire un don (e2e)',
        'post_name' => 'don',
        'guid' => home_url('/don/'),
    ]);
    update_post_meta($donation_page_id, '_wp_page_template', 'page-don');
}

if (!get_page_by_path('legs')) {
    $legacy_giving_page_id = aif_e2e_insert_post([
        'post_title' => 'Legs et donations (e2e)',
        'post_name' => 'legs',
        'guid' => home_url('/legs/'),
    ]);
    update_post_meta($legacy_giving_page_id, '_wp_page_template', 'page-legs');
}

if (!get_page_by_path('formulaire-legs')) {
    $legacy_giving_form = implode('', [
        '[contact-form to="e2e@example.test" subject="Demande de brochure - legs (e2e)"]',
        '[contact-field label="Civilité" type="radio" options="Madame,Monsieur,Autre" required="1"]',
        '[contact-field label="Nom" type="name" required="1"]',
        '[contact-field label="Prénom" type="name" required="1"]',
        '[contact-field label="Adresse" type="textarea" required="1"]',
        '[contact-field label="Code Postal" type="text" required="1"]',
        '[contact-field label="Ville" type="text" required="1"]',
        '[contact-field label="E-mail" type="email" required="1"]',
        '[contact-field label="Téléphone" type="telephone" required="1"]',
        '[contact-field label="Je souhaite recevoir la brochure" type="checkbox-multiple" options="Par courrier postal,Par email"]',
        '[contact-field label="J\'accepte que mes données soient traitées par Amnesty International France" type="consent" consentType="explicit" required="1"]',
        '[/contact-form]',
    ]);

    aif_e2e_insert_post([
        'post_title' => 'Formulaire legs (e2e)',
        'post_name' => 'formulaire-legs',
        'post_content' => $legacy_giving_form,
        'guid' => home_url('/formulaire-legs/'),
    ]);
}

if (!get_page_by_path('fondation')) {
    $foundation_page_id = aif_e2e_insert_post([
        'post_title' => 'Fondation Amnesty International France (e2e)',
        'post_name' => 'fondation',
        'guid' => home_url('/fondation/'),
    ]);
    update_post_meta($foundation_page_id, '_wp_page_template', 'page-fondation');
}

if (!get_page_by_path('formulaire-foundation')) {
    $foundation_form = implode('', [
        '[contact-form to="e2e@example.test" subject="Contact fondation (e2e)"]',
        '[contact-field label="Civilité" type="radio" options="Madame,Monsieur,Autre"]',
        '[contact-field label="Nom" type="name" required="1"]',
        '[contact-field label="Prénom" type="name" required="1"]',
        '[contact-field label="E-mail" type="email" required="1"]',
        '[contact-field label="Téléphone" type="telephone"]',
        '[contact-field label="Un message à nous laisser ?" type="textarea"]',
        '[contact-field label="Je souhaite recevoir des informations sur la Fondation Amnesty International France par courrier postal." type="checkbox"]',
        '[/contact-form]',
    ]);

    aif_e2e_insert_post([
        'post_title' => 'Formulaire fondation (e2e)',
        'post_name' => 'formulaire-foundation',
        'post_content' => $foundation_form,
        'guid' => home_url('/formulaire-foundation/'),
    ]);
}
