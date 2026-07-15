#!/usr/bin/env bash
set -euo pipefail

yarn env:e2e run cli wp plugin activate aif-e2e-support
yarn env:e2e run cli wp theme activate humanity-theme
yarn env:e2e run cli wp option update permalink_structure '/%postname%/'
yarn env:e2e run cli wp rewrite flush

yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("mot-de-passe-oublie")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "Mot de passe oublié",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "mot-de-passe-oublie",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/mot-de-passe-oublie/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

clean_post_cache((int) $wpdb->insert_id);
'

yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("newsletter")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "<!-- wp:pattern {\"slug\":\"amnesty/page-nl-content\"} /-->",
    "post_title" => "Newsletter",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "newsletter",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/newsletter/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

clean_post_cache((int) $wpdb->insert_id);
'

# Homepage: a static front page with identifiable content, so the "homepage"
# journey exercises a real marketing page instead of WordPress'"'"'s default
# blog-archive fallback (no front page is configured out of the box).
yarn env:e2e run cli wp eval '
global $wpdb;

$existing = get_page_by_path("accueil-e2e", OBJECT, "page");
if ($existing) {
    update_option("show_on_front", "page");
    update_option("page_on_front", $existing->ID);
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "<!-- wp:heading {\"level\":1} --><h1 class=\"wp-block-heading\">Bienvenue chez Amnesty International France</h1><!-- /wp:heading -->",
    "post_title" => "Accueil (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "accueil-e2e",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/accueil-e2e/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

$post_id = (int) $wpdb->insert_id;
clean_post_cache($post_id);

update_option("show_on_front", "page");
update_option("page_on_front", $post_id);
'

# Search: deliberately NOT seeding a page for `amnesty_search_page`. Verified
# directly against the running e2e env that native WordPress search already
# works with no extra seeding at all: GET /?s=<term> returns 200 with a
# correct "Search Results for ..." title and .post--result markup. See the
# conversation notes for why a custom search page isn'"'"'t used here: the
# on-site search trigger ("Ouvrir la recherche") is a Jetpack Instant Search
# integration (jetpack-search-filter__link) and Jetpack isn'"'"'t installed in
# this e2e stack, and the theme'"'"'s own "amnesty/search" pattern fatals on a
# pre-existing production bug (amnesty_sort_chronicle_archive_block_query()
# type-hint mismatch) unrelated to this seeding work.

# Petition: seeded via a raw $wpdb insert (like the pages above) rather than
# wp_insert_post()/`wp post create`, so the acf/save_post save_post hook chain
# - which includes create_petition() posting to the real Salesforce API - is
# never triggered during seeding. date_de_fin/type/objectif_signatures are
# regular postmeta (not real ACF, which isn'"'"'t installed in this environment)
# consumed by aif-e2e-support.php'"'"'s get_field() postmeta-fallback stub.
yarn env:e2e run cli wp eval '
global $wpdb;

$existing = get_page_by_path("aif-e2e-petition", OBJECT, "petition");
if ($existing) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "Justice pour toustes (e2e)",
    "post_excerpt" => "Nous demandons la libération immédiate des militant·e·s emprisonné·e·s injustement.",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "aif-e2e-petition",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/petitions/aif-e2e-petition/"),
    "menu_order" => 0,
    "post_type" => "petition",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

$post_id = (int) $wpdb->insert_id;
clean_post_cache($post_id);

update_post_meta($post_id, "type", "petition");
update_post_meta($post_id, "date_de_fin", date("Y-m-d", strtotime("+1 year")));
update_post_meta($post_id, "objectif_signatures", 1000);
'

# Navigation: no menu is assigned to any location out of the box, so
# amnesty_nav("main-menu") renders nothing.
yarn env:e2e run cli wp eval '
$menu_name = "Main Menu (e2e)";

if (wp_get_nav_menu_object($menu_name)) {
    return;
}

$menu_id = wp_create_nav_menu($menu_name);

wp_update_nav_menu_item($menu_id, 0, [
    "menu-item-title" => "Accueil",
    "menu-item-url" => home_url("/accueil-e2e/"),
    "menu-item-status" => "publish",
]);

wp_update_nav_menu_item($menu_id, 0, [
    "menu-item-title" => "Nos pétitions",
    "menu-item-url" => home_url("/petitions/"),
    "menu-item-status" => "publish",
]);

$locations = get_theme_mod("nav_menu_locations", []);
$locations["main-menu"] = $menu_id;
set_theme_mod("nav_menu_locations", $locations);
'
