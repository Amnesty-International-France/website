<?php

/*
Plugin Name: AIF RSS Importer
Description: Import automatique du flux RSS amnesty international france
Version: 1.0
Author: Les-Tilleuls.coop (mehdi@les-tilleuls.coop)
*/

if (! defined('ABSPATH')) {
    exit;
}

define('AIFRSS_OPTION_KEY', 'aifrss_options');
define('AIFRSS_CRON_HOOK', 'aifrss_rss_import_event');

function aifrss_activate(): void
{
    $defaults = [
        'feed_url'   => 'https://www.amnesty.org/fr/latest/feed/',
        'items'      => 20,
        'frequency'  => 'daily',
        'post_status' => 'publish',
    ];

    if (false === get_option(AIFRSS_OPTION_KEY)) {
        add_option(AIFRSS_OPTION_KEY, $defaults);
    }

    if (! wp_next_scheduled(AIFRSS_CRON_HOOK)) {
        $options = get_option(AIFRSS_CRON_HOOK, $defaults);
        $freq = $options['frequency'] ?? 'daily';
        wp_schedule_event(time(), $freq, AIFRSS_CRON_HOOK);
    }
}
register_activation_hook(__FILE__, 'aifrss_activate');

function aifrss_deactivate(): void
{
    wp_clear_scheduled_hook(AIFRSS_CRON_HOOK);
}
register_deactivation_hook(__FILE__, 'aifrss_deactivate');

add_filter('cron_schedules', 'aifrss_add_cron_schedules');
function aifrss_add_cron_schedules($schedules): array
{
    $schedules['every_three_days'] = [
        'interval' => 3 * DAY_IN_SECONDS,
        'display'  => __('Tous les 3 jours'),
    ];

    $schedules['every_seven_days'] = [
        'interval' => 7 * DAY_IN_SECONDS,
        'display'  => __('Une fois par semaine'),
    ];
    return $schedules;
}

add_action('admin_menu', 'aifrss_admin_menu');
function aifrss_admin_menu(): void
{
    add_options_page(
        'AIF RSS',
        'AIF RSS',
        'manage_options',
        'aifrss-rss-importer',
        'aifrss_options_page'
    );
}

add_action('admin_init', 'aifrss_register_settings');
function aifrss_register_settings(): void
{
    register_setting('aifrss_options_group', AIFRSS_OPTION_KEY, 'aifrss_options_sanitize');
}

function aifrss_options_sanitize($input): array
{
    $old = get_option(AIFRSS_OPTION_KEY, []);

    $san = [];
    $san['feed_url'] = esc_url_raw($input['feed_url']) ?? 'https://www.amnesty.org/fr/latest/feed/';
    $san['items']    = absint($input['items']) ?? 20;
    $allowed_freqs = ['hourly', 'twicedaily', 'daily', 'every_three_days', 'every_seven_days'];
    $san['frequency'] = in_array($input['frequency'], $allowed_freqs, true) ? $input['frequency'] : 'hourly';
    $san['post_status'] = in_array($input['post_status'], ['publish','draft'], true) ? $input['post_status'] : 'publish';

    if (empty($old) || (isset($old['frequency']) && $old['frequency'] !== $san['frequency'])) {
        wp_clear_scheduled_hook(AIFRSS_CRON_HOOK);
        wp_schedule_event(time(), $san['frequency'], AIFRSS_CRON_HOOK);
    }

    return $san;
}

function aifrss_options_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    require_once 'aif-rss-settings-page.php';
}

add_action('admin_post_aifrss_run_now', 'aifrss_admin_run_now');
function aifrss_admin_run_now(): void
{
    if (! current_user_can('manage_options')) {
        wp_die('Permission denied');
    }
    check_admin_referer('aifrss_run_now_nonce');

    $count = aifrss_import_rss_feed();

    $redirect = add_query_arg([ 'page' => 'aifrss-rss-importer', 'aifrss_run' => 1, 'count' => $count ], admin_url('options-general.php'));
    wp_safe_redirect($redirect);
    exit;
}

add_action('admin_notices', 'aifrss_admin_notices');
function aifrss_admin_notices(): void
{
    if (isset($_GET['aifrss_run']) && current_user_can('manage_options')) {
        $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
        if ($count > 0) {
            echo '<div class="notice notice-success is-dismissible"><p>Import de ' . esc_html($count) . ' nouveaux communiqués réalisé avec succès.</p></div>';
        } else {
            echo '<div class="notice notice-warning is-dismissible"><p>Aucun nouveau communiqué de presse importé.</p></div>';
        }
    }
}

add_action(AIFRSS_CRON_HOOK, 'aifrss_import_rss_feed');
function aifrss_import_rss_feed(): int
{
    $post_type = 'press-release';
    if (!post_type_exists($post_type)) {
        update_option('aifrss_last_error', 'Le type de poste "communiqué de presse" n\'existe pas ! Les imports ne fonctionneront pas !');
        return 0;
    }

    $options = get_option(AIFRSS_OPTION_KEY);
    if (empty($options['feed_url'])) {
        update_option('aifrss_last_error', 'Aucune URL de flux configurée.');
        return 0;
    }

    $feed_url = esc_url_raw($options['feed_url']);
    $max_items = absint($options['items']) ?? 20;
    $post_status = $options['post_status'] ?? 'publish';

    $rss = fetch_feed($feed_url);
    if (is_wp_error($rss)) {
        update_option('aifrss_last_error', $rss->get_error_message());
        return 0;
    }

    $maxitems = $rss->get_item_quantity($max_items);
    $items = $rss->get_items(0, $maxitems);
    $created = 0;

    foreach ($items as $item) {
        $guid = method_exists($item, 'get_id') ? $item->get_id() : '';
        if (empty($guid)) {
            $guid = $item->get_permalink();
        }
        $guid = (string) $guid;

        $existing = get_posts([
            'post_type'      => 'press-release',
            'meta_key'       => 'aifrss_guid',
            'meta_value'     => $guid,
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);
        if (!empty($existing)) {
            continue;
        }

        $title = html_entity_decode((string) $item->get_title(), ENT_QUOTES | ENT_XML1);
        $content = (string) $item->get_content();
        if (empty($content)) {
            $content = (string) $item->get_description();
        }

        $new_post = [
            'post_title'   => wp_strip_all_tags($title),
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => 'press-release',
            'post_author'  => 1,
        ];

        $post_id = wp_insert_post($new_post);
        if ($post_id && ! is_wp_error($post_id)) {
            add_post_meta($post_id, 'aifrss_guid', $guid, true);
            add_post_meta($post_id, 'aifrss_source', (string) $item->get_permalink(), true);
            add_post_meta($post_id, 'aifrss_pubdate', ($item->get_date('Y-m-d H:i:s')) ? $item->get_date('Y-m-d H:i:s') : '', true);
            $created++;
        }
    }

    update_option('aifrss_last_run', current_time('d/m/Y H:i:s'));
    update_option('aifrss_last_count', $created);
    delete_option('aifrss_last_error');

    return $created;
}
