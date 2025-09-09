<?php

declare(strict_types=1);

add_action('admin_menu', 'amnesty_training_add_settings_page');

function amnesty_training_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=training',
        'Réglages Formations',
        'Réglages',
        'manage_options',
        'training_settings',
        'amnesty_training_settings_page_callback'
    );
}

function amnesty_training_settings_page_callback() {
    if (
        isset($_POST['amnesty_training_settings_nonce']) &&
        wp_verify_nonce($_POST['amnesty_training_settings_nonce'], 'save_training_settings')
    ) {
        amnesty_training_process_settings_form();
        echo '<div class="notice notice-success is-dismissible"><p>Réglages enregistrés.</p></div>';
    }

    $chapo = get_option('training_global_chapo', '');

    ?>
    <div class="wrap">
        <h1>Réglages pour les Formations</h1>
        <form method="post">
            <?php
            wp_nonce_field('save_training_settings', 'amnesty_training_settings_nonce');
            ?>

            <h2>Texte du chapo</h2>
            <p>Ce texte s'affichera en haut de la page d'archive des formations.</p>
            <textarea name="training_global_chapo" rows="5" class="large-text"><?php echo esc_textarea(stripslashes($chapo)); ?></textarea>

            <?php submit_button('Enregistrer les réglages'); ?>
        </form>
    </div>
    <?php
}

function amnesty_training_process_settings_form() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['training_global_chapo'])) {
        $chapo_value = sanitize_textarea_field($_POST['training_global_chapo']);
        update_option('training_global_chapo', $chapo_value);
    }
}
