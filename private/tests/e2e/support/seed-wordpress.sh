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
