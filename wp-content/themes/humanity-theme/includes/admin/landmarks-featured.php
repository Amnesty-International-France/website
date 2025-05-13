<?php

declare( strict_types = 1 );

add_action('admin_menu', 'amnesty_add_featured_admin_page');

function amnesty_add_featured_admin_page() {
    add_submenu_page(
        'edit.php?post_type=landmark',
        'Repères mis en avant',
        'Repères mis en avant',
        'edit_posts',
        'featured_landmarks',
        'amnesty_featured_admin_page_callback'
    );
}

function amnesty_featured_admin_page_callback() {
    if (
        isset($_POST['amnesty_featured_nonce']) &&
        wp_verify_nonce($_POST['amnesty_featured_nonce'], 'save_featured_selection')
    ) {
        amnesty_process_featured_selection();
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

    echo '<div class="wrap"><h1>Choisir les repères à mettre en avant</h1>';
    echo '<form method="post">';
    wp_nonce_field('save_featured_selection', 'amnesty_featured_nonce');

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

    echo '<p><input type="submit" class="button-primary" value="Enregistrer"></p>';
    echo '</form></div>';
}


function amnesty_process_featured_selection() {
    if (!current_user_can('edit_posts')) return;

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
}

function amnesty_get_featured_landmarks() {
    return new WP_Query([
        'post_type'      => 'landmark',
        'posts_per_page' => 3,
        'meta_key'       => '_featured_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_featured_order',
                'compare' => 'EXISTS'
            ]
        ],
    ]);
}






