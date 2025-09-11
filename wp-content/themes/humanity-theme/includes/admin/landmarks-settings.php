<?php

declare(strict_types=1);

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'landmark_page_landmark_settings') {
        return;
    }
    wp_enqueue_media();
});

add_action('admin_menu', 'amnesty_add_settings_page');

function amnesty_add_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=landmark',
        'Réglages Repères',
        'Réglages',
        'manage_options',
        'landmark_settings',
        'amnesty_settings_page_callback'
    );
}

function amnesty_settings_page_callback()
{
    if (
        isset($_POST['amnesty_settings_nonce']) &&
        wp_verify_nonce($_POST['amnesty_settings_nonce'], 'save_landmark_settings')
    ) {
        amnesty_process_settings_form();
    }

    $all_landmarks = get_posts([
        'post_type' => 'landmark',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $featured_ids = get_posts([
        'post_type'   => 'landmark',
        'numberposts' => 3,
        'meta_key'    => '_featured_order',
        'orderby'     => 'meta_value_num',
        'order'       => 'ASC',
        'fields'      => 'ids',
        'meta_query'  => [
            [
                'key'   => '_is_featured',
                'value' => '1',
            ],
        ],
    ]);

    $image_id = get_option('landmark_global_image_id');
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';

    $chapo = get_option('landmark_global_chapo', '');

    echo '<div class="wrap"><h1>Réglages pour les Repères</h1>';
    echo '<form method="post">';
    wp_nonce_field('save_landmark_settings', 'amnesty_settings_nonce');

    echo '<h2>Image pour le CPT Repères</h2>';
    echo '<div id="landmark-image-preview">';
    if ($image_url) {
        echo '<img src="' . esc_url($image_url) . '" style="max-width:200px;" />';
    }
    echo '</div>';
    echo '<input type="hidden" name="landmark_global_image_id" id="landmark_global_image_id" value="' . esc_attr($image_id) . '" />';
    echo '<button type="button" class="button" id="upload-landmark-image">Choisir une image</button>';

    echo '<h2>Repères mis en avant</h2>';
    echo '<p>Sélectionnez l\'ordre des repères :</p>';

    for ($i = 1; $i <= 3; $i++) {
        echo '<p><label for="featured_order_' . $i . '">Repère ' . $i . ' :</label>';
        echo '<select name="featured_order[' . $i . ']" id="featured_order_' . $i . '">';
        echo '<option value="">Choisir un repère</option>';

        foreach ($all_landmarks as $post) {
            $selected = (isset($featured_ids[$i - 1]) && $post->ID == $featured_ids[$i - 1]) ? 'selected' : '';
            echo '<option value="' . esc_attr($post->ID) . '" ' . $selected . '>' . esc_html($post->post_title) . '</option>';
        }

        echo '</select></p>';
    }

    echo '<h2>Texte du chapo</h2>';
    echo '<textarea name="landmark_global_chapo" rows="5" style="width:100%">' . esc_textarea(stripslashes($chapo)) . '</textarea>';

    echo '<p><input type="submit" class="button-primary" value="Enregistrer les réglages"></p>';
    echo '</form></div>';
}

function amnesty_process_settings_form()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['featured_order']) && is_array($_POST['featured_order'])) {
        $featured_order = array_map('intval', $_POST['featured_order']);
        $featured_order = array_filter($featured_order);
        $featured_order = array_slice($featured_order, 0, 3);

        $all_landmarks = get_posts([
            'post_type' => 'landmark',
            'numberposts' => -1,
            'fields' => 'ids',
        ]);

        $order_index = 1;
        foreach ($featured_order as $landmark_id) {
            if (in_array($landmark_id, $all_landmarks)) {
                update_post_meta($landmark_id, '_is_featured', '1');
                update_post_meta($landmark_id, '_featured_order', $order_index);
                $order_index++;
            }
        }

        foreach ($all_landmarks as $post_id) {
            if (!in_array($post_id, $featured_order)) {
                delete_post_meta($post_id, '_is_featured');
                delete_post_meta($post_id, '_featured_order');
            }
        }
    }

    if (!empty($_POST['landmark_global_image_id'])) {
        update_option('landmark_global_image_id', intval($_POST['landmark_global_image_id']));
    }

    if (isset($_POST['landmark_global_chapo'])) {
        update_option('landmark_global_chapo', sanitize_textarea_field($_POST['landmark_global_chapo']));
    }
}

add_action('admin_footer', function () {
    ?>
    <script>
        jQuery(document).ready(function ($) {
            let frame;
            $('#upload-landmark-image').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Choisir une image',
                    button: {
                        text: 'Utiliser cette image'
                    },
                    multiple: false
                });
                frame.on('select', function () {
                    const attachment = frame.state().get('selection').first().toJSON();
                    $('#landmark_global_image_id').val(attachment.id);
                    $('#landmark-image-preview').html('<img src="' + attachment.url + '" style="max-width:200px;" />');
                });
                frame.open();
            });
        });
    </script>
    <?php
});

function amnesty_get_featured_landmarks()
{
    return new WP_Query([
        'post_type'      => 'landmark',
        'posts_per_page' => 3,
        'meta_key'       => '_featured_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_featured_order',
                'compare' => 'EXISTS',
            ],
        ],
    ]);
}

// Exclude featured landmarks from the main query on the archive page
function amnesty_exclude_featured_landmarks($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('landmark')) {
        $featured_query = amnesty_get_featured_landmarks();
        $featured_ids = [];

        if ($featured_query->have_posts()) {
            while ($featured_query->have_posts()) {
                $featured_query->the_post();
                $featured_ids[] = get_the_ID();
            }
            wp_reset_postdata();
        }

        if (!empty($featured_ids)) {
            $query->set('post__not_in', $featured_ids);
        }
    }
}
add_action('pre_get_posts', 'amnesty_exclude_featured_landmarks');
