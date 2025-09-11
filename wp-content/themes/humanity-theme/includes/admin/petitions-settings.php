<?php

declare(strict_types=1);

add_action('admin_menu', 'petition_add_settings_page');

function petition_add_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=petition',
        'Réglages Petition',
        'Réglages',
        'manage_options',
        'petition_settings',
        'petition_settings_page_callback'
    );
}

function petition_settings_page_callback()
{
    if (
        isset($_POST['petition_settings_nonce']) &&
        wp_verify_nonce($_POST['petition_settings_nonce'], 'save_petition_settings')
    ) {
        petition_process_settings_form();
    }

    $donate_label = get_option('petition_donate_button_label', 'Faire un don');

    echo '<div class="wrap"><h1>Réglages pour les Petitions</h1>';
    echo '<form method="post">';
    wp_nonce_field('save_petition_settings', 'petition_settings_nonce');

    echo '<h2>Label du bouton Faire un don</h2>';
    echo '<p><input type="text" name="petition_donate_button_label" value="' . esc_attr($donate_label) . '" style="width:300px;" /></p>';

    echo '<p><input type="submit" class="button-primary" value="Enregistrer les réglages"></p>';
    echo '</form></div>';
}

function petition_process_settings_form()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['petition_donate_button_label'])) {
        update_option('petition_donate_button_label', sanitize_text_field($_POST['petition_donate_button_label']));
    }
}

function amnesty_add_petition_thanks_rewrite_rule()
{
    add_rewrite_rule(
        '^petitions/([^/]+)/merci/?$',
        'index.php?petition=$matches[1]&thanks=1',
        'top'
    );
}
add_action('init', 'amnesty_add_petition_thanks_rewrite_rule');

function amnesty_add_query_vars($vars)
{
    $vars[] = 'thanks';
    return $vars;
}
add_filter('query_vars', 'amnesty_add_query_vars');
