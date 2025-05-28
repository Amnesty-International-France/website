<?php

declare(strict_types=1);

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'fiche_pays_page_countries_settings') {
        return;
    }
    wp_enqueue_media();
});

add_action('admin_menu', 'amnesty_add_countries_settings_page');

function amnesty_add_countries_settings_page() {
    add_submenu_page(
        'edit.php?post_type=fiche_pays',
        'Réglages Pays',
        'Réglages Pays',
        'manage_options',
        'countries_settings',
        'amnesty_countries_settings_page_callback'
    );
}

function amnesty_countries_settings_page_callback() {
    if (
        isset($_POST['amnesty_settings_nonce']) &&
        wp_verify_nonce($_POST['amnesty_settings_nonce'], 'save_countries_settings')
    ) {
        amnesty_process_countries_settings_form();
    }

    $doc_id = get_option('countries_global_document_id');
    $doc_url = $doc_id ? wp_get_attachment_url($doc_id) : '';
    $doc_post = $doc_id ? get_post($doc_id) : null;
    $doc_filename = $doc_post ? $doc_post->post_title . '.' . pathinfo($doc_url, PATHINFO_EXTENSION) : '';

    $chapo = get_option('countries_global_chapo', '');

    echo '<div class="wrap"><h1>Réglages pour les Pays</h1>';
    echo '<form method="post">';
    wp_nonce_field('save_countries_settings', 'amnesty_settings_nonce');

    echo '<h2>Document du rapport Amnesty pour les pays</h2>';
    echo '<div id="countries-document-preview">';
    if ($doc_url && $doc_filename) {
        echo '<p><strong>Document actuel :</strong> <a href="' . esc_url($doc_url) . '" target="_blank">' . esc_html($doc_filename) . '</a></p>';
    }
    echo '</div>';

    echo '<input type="hidden" name="countries_global_document_id" id="countries_global_document_id" value="' . esc_attr($doc_id) . '" />';
    echo '<button type="button" class="button" id="upload-countries-report-document">Choisir un document</button>';

    echo '<h2>Texte du chapo</h2>';
    echo '<textarea name="countries_global_chapo" rows="5" style="width:100%">' . esc_textarea($chapo) . '</textarea>';

    echo '<p><input type="submit" class="button-primary" value="Enregistrer les réglages"></p>';
    echo '</form></div>';
}

add_action('admin_footer', function () {
    ?>
    <script>
        jQuery(document).ready(function ($) {
            let frame;
            $('#upload-countries-report-document').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Choisir un document PDF',
                    button: {
                        text: 'Utiliser ce document'
                    },
                    multiple: false,
                    library: {
                        type: 'application/pdf'
                    }
                });
                frame.on('select', function () {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#countries_global_document_id').val(attachment.id);
                    $('#countries-document-preview').html(
                        '<p><strong>Document sélectionné :</strong> ' +
                        '<a href="' + attachment.url + '" target="_blank">' +
                        attachment.filename +
                        '</a></p>'
                    );
                });
                frame.open();
            });
        });
    </script>
    <?php
});

function amnesty_process_countries_settings_form() {
    if (!current_user_can('manage_options')) return;

    if (!empty($_POST['countries_global_document_id'])) {
        update_option('countries_global_document_id', intval($_POST['countries_global_document_id']));
    }

    if (isset($_POST['countries_global_chapo'])) {
        update_option('countries_global_chapo', sanitize_textarea_field($_POST['countries_global_chapo']));
    }
}
