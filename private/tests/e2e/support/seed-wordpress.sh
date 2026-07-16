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

# Country: the petition/urgent-action signature forms'"'"' country <select> is
# browser-required, and its options come exclusively from published
# fiche_pays posts (cached for an hour in the amnesty_fiche_pays_list
# transient) - with none seeded, the form has no valid option to select and
# fails HTML5 validation on submit, silently blocking every signature.
yarn env:e2e run cli wp eval '
global $wpdb;

delete_transient("amnesty_fiche_pays_list");

if (get_page_by_path("france-e2e", OBJECT, "fiche_pays")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "France",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "france-e2e",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/pays/france-e2e/"),
    "menu_order" => 0,
    "post_type" => "fiche_pays",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

clean_post_cache((int) $wpdb->insert_id);
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

# Donation page: templates/page-don.html is picked up automatically for a
# page with slug "don" (also set _wp_page_template explicitly to match how
# production configures it). Its "amnesty/aside-donation-sticky" pattern
# embeds the donation-calculator block with a hardcoded href/rate, so the
# page'"'"'s own (empty) content is irrelevant to that calculator.
yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("don")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "Faire un don (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "don",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/don/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

$post_id = (int) $wpdb->insert_id;
clean_post_cache($post_id);

update_post_meta($post_id, "_wp_page_template", "page-don");
'

# Legs page: templates/page-legs.html (slug "legs") embeds parts/legs-form.html
# -> patterns/form-legs.php, which fetches a SEPARATE page ("formulaire-legs")
# and echoes its content through the_content. That real production page is a
# Jetpack contact-form block living only in the prod DB (not in this repo,
# and Jetpack isn'"'"'t installed in this e2e stack to render/process it anyway),
# so we seed our OWN representative static form markup here - enough to
# verify the "Demander notre brochure" link reveals a real, fillable form,
# but NOT a real Jetpack submission (no plugin to process it in e2e).
yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("legs")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "Legs et donations (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "legs",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/legs/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

$post_id = (int) $wpdb->insert_id;
clean_post_cache($post_id);

update_post_meta($post_id, "_wp_page_template", "page-legs");
'

yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("formulaire-legs")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

// Field set/labels match the real "formulaire-legs" page as rendered at
// /nous-soutenir/legs/ in production (a Jetpack Forms block) - civility,
// nom, prénom, adresse, code postal, ville, e-mail, téléphone, a two-option
// "je souhaite recevoir la brochure" checkbox group, and a consent checkbox.
// This is plain static markup, not a real Jetpack block: the plugin isn'"'"'t
// installed in this e2e stack (see the spec file for why), so this only
// supports verifying the form is reachable and fillable, not a real
// submission/thank-you flow.
$form_content = "<div class=\"wp-block-jetpack-contact-form\" data-test=\"contact-form\">"
    . "<form>"
    . "<fieldset><legend>Civilité</legend>"
    . "<label><input type=\"radio\" name=\"civilite\" value=\"Madame\"> Madame</label>"
    . "<label><input type=\"radio\" name=\"civilite\" value=\"Monsieur\"> Monsieur</label>"
    . "<label><input type=\"radio\" name=\"civilite\" value=\"Autre\"> Autre</label>"
    . "</fieldset>"
    . "<p><label for=\"legs-nom\">Nom</label><input type=\"text\" id=\"legs-nom\" name=\"nom\" required></p>"
    . "<p><label for=\"legs-prenom\">Prénom</label><input type=\"text\" id=\"legs-prenom\" name=\"prenom\" required></p>"
    . "<p><label for=\"legs-adresse\">Adresse</label><textarea id=\"legs-adresse\" name=\"adresse\" required></textarea></p>"
    . "<p><label for=\"legs-codepostal\">Code Postal</label><input type=\"text\" id=\"legs-codepostal\" name=\"codepostal\" required></p>"
    . "<p><label for=\"legs-ville\">Ville</label><input type=\"text\" id=\"legs-ville\" name=\"ville\" required></p>"
    . "<p><label for=\"legs-email\">E-mail</label><input type=\"email\" id=\"legs-email\" name=\"email\" required></p>"
    . "<p><label for=\"legs-telephone\">Téléphone</label><input type=\"tel\" id=\"legs-telephone\" name=\"telephone\" required></p>"
    . "<fieldset><legend>Je souhaite recevoir la brochure</legend>"
    . "<label><input type=\"checkbox\" name=\"brochure[]\" value=\"Par courrier postal\"> Par courrier postal</label>"
    . "<label><input type=\"checkbox\" name=\"brochure[]\" value=\"Par email\"> Par email</label>"
    . "</fieldset>"
    . "<p><label><input type=\"checkbox\" name=\"consent\" required> J'"'"'accepte que mes données soient traitées par Amnesty International France</label></p>"
    . "<button type=\"submit\">Envoyer</button>"
    . "</form>"
    . "</div>";

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => $form_content,
    "post_title" => "Formulaire legs (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "formulaire-legs",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/formulaire-legs/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

clean_post_cache((int) $wpdb->insert_id);
'
