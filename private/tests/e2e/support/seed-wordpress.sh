#!/usr/bin/env bash
set -euo pipefail

yarn env:e2e run cli wp plugin activate aif-e2e-support
yarn env:e2e run cli wp theme activate humanity-theme
yarn env:e2e run cli wp option update permalink_structure '/%postname%/'
yarn env:e2e run cli wp rewrite flush

# French locale: the site's own content is hardcoded French already, but
# plugin-generated UI strings (e.g. Jetpack Forms'"'"' submit button/success
# message) go through WordPress i18n and stay in English without this -
# install/activate both the core and Jetpack fr_FR translations so specs can
# assert on the real French copy a visitor actually sees.
yarn env:e2e run cli wp language core install fr_FR
yarn env:e2e run cli wp site switch-language fr_FR
yarn env:e2e run cli wp language plugin install jetpack fr_FR

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
// /nous-soutenir/legs/ in production. Uses Jetpack classic
// [contact-form] shortcode (still supported by the "contact-form" module
// activated via .wp-env.e2e.json'"'"'s afterStart script) rather than hand-authoring
// the current block-comment markup/attributes, which is more likely to drift
// across Jetpack versions. Jetpack auto-detects "localhost" as offline/dev
// mode (no account/connection needed), so the module renders and processes
// a REAL AJAX submission here - unlike the rest of this e2e stack, this one
// genuinely exercises Jetpack'"'"'s own form validation and success state.
$form_content = "[contact-form to=\"e2e@example.test\" subject=\"Demande de brochure - legs (e2e)\"]"
    . "[contact-field label=\"Civilité\" type=\"radio\" options=\"Madame,Monsieur,Autre\" required=\"1\"]"
    . "[contact-field label=\"Nom\" type=\"name\" required=\"1\"]"
    . "[contact-field label=\"Prénom\" type=\"name\" required=\"1\"]"
    . "[contact-field label=\"Adresse\" type=\"textarea\" required=\"1\"]"
    . "[contact-field label=\"Code Postal\" type=\"text\" required=\"1\"]"
    . "[contact-field label=\"Ville\" type=\"text\" required=\"1\"]"
    . "[contact-field label=\"E-mail\" type=\"email\" required=\"1\"]"
    . "[contact-field label=\"Téléphone\" type=\"telephone\" required=\"1\"]"
    . "[contact-field label=\"Je souhaite recevoir la brochure\" type=\"checkbox-multiple\" options=\"Par courrier postal,Par email\"]"
    // consentType="explicit" renders a real, checkable checkbox. Without it,
    // Jetpack defaults to "implicit" consent (a hidden input pre-set to
    // "Oui") whose value the Interactivity API'"'"'s own client-side validation
    // state doesn'"'"'t seed from - it fails "champ obligatoire" on submit even
    // though the field is never shown to (or actionable by) a visitor.
    . "[contact-field label=\"J'"'"'accepte que mes données soient traitées par Amnesty International France\" type=\"consent\" consentType=\"explicit\" required=\"1\"]"
    . "[/contact-form]";

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

# Foundation page: templates/page-fondation.html (slug "fondation") embeds
# parts/foundation-form.html -> patterns/form-foundation.php, which fetches a
# SEPARATE page (note the English spelling: "formulaire-foundation", not
# "formulaire-fondation" - must match exactly for get_page_by_path() to find
# it) and echoes its content through the_content, exactly like the legs form.
# Verified against the real page at /fondation-amnesty-international-france/:
# Civilité (optional radio), Nom/Prénom/E-mail (required), Téléphone
# (optional, no country selector here for simplicity), an optional message,
# and an optional "receive by post" checkbox - no consent field this time.
yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("fondation")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => "",
    "post_title" => "Fondation Amnesty International France (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "fondation",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/fondation/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

$post_id = (int) $wpdb->insert_id;
clean_post_cache($post_id);

update_post_meta($post_id, "_wp_page_template", "page-fondation");
'

yarn env:e2e run cli wp eval '
global $wpdb;

if (get_page_by_path("formulaire-foundation")) {
    return;
}

$now = current_time("mysql");
$now_gmt = current_time("mysql", true);

$form_content = "[contact-form to=\"e2e@example.test\" subject=\"Contact fondation (e2e)\"]"
    . "[contact-field label=\"Civilité\" type=\"radio\" options=\"Madame,Monsieur,Autre\"]"
    . "[contact-field label=\"Nom\" type=\"name\" required=\"1\"]"
    . "[contact-field label=\"Prénom\" type=\"name\" required=\"1\"]"
    . "[contact-field label=\"E-mail\" type=\"email\" required=\"1\"]"
    . "[contact-field label=\"Téléphone\" type=\"telephone\"]"
    . "[contact-field label=\"Un message à nous laisser ?\" type=\"textarea\"]"
    . "[contact-field label=\"Je souhaite recevoir des informations sur la Fondation Amnesty International France par courrier postal.\" type=\"checkbox\"]"
    . "[/contact-form]";

$wpdb->insert($wpdb->posts, [
    "post_author" => 1,
    "post_date" => $now,
    "post_date_gmt" => $now_gmt,
    "post_content" => $form_content,
    "post_title" => "Formulaire fondation (e2e)",
    "post_excerpt" => "",
    "post_status" => "publish",
    "comment_status" => "closed",
    "ping_status" => "closed",
    "post_password" => "",
    "post_name" => "formulaire-foundation",
    "to_ping" => "",
    "pinged" => "",
    "post_modified" => $now,
    "post_modified_gmt" => $now_gmt,
    "post_content_filtered" => "",
    "post_parent" => 0,
    "guid" => home_url("/formulaire-foundation/"),
    "menu_order" => 0,
    "post_type" => "page",
    "post_mime_type" => "",
    "comment_count" => 0,
]);

clean_post_cache((int) $wpdb->insert_id);
'
