<?php

/**
 * Ajoute automatiquement l'ID de la pièce jointe comme suffixe à son slug lors de sa création.
 *
 * @param int $attachment_ID L'ID de la pièce jointe qui vient d'être créée.
 */
function add_id_suffix_to_attachment_slug($attachment_ID)
{

    $attachment_post = get_post($attachment_ID);
    $current_slug    = $attachment_post->post_name;
    $id_suffix       = '-' . $attachment_ID;

    if (str_ends_with($current_slug, $id_suffix)) {
        return;
    }

    $new_slug = $current_slug . $id_suffix;

    $post_data = [
        'ID'        => $attachment_ID,
        'post_name' => $new_slug,
    ];

    remove_action('add_attachment', 'add_id_suffix_to_attachment_slug');

    wp_update_post($post_data);

    add_action('add_attachment', 'add_id_suffix_to_attachment_slug');
}

add_action('add_attachment', 'add_id_suffix_to_attachment_slug');

/**
 * Affiche une notice dans l'admin pour lancer le script de mise à jour des slugs.
 */
function show_update_slugs_admin_notice_batch()
{
    if (! isset($_GET['run_media_rename_batch'])) {
        if (current_user_can('manage_options') && ! get_option('slugs_updated_notice_dismissed')) {
            $run_url = add_query_arg('run_media_rename_batch', 'true', admin_url('index.php'));
            echo '<div class="notice notice-warning is-dismissible"><p><strong>Action requise :</strong> Mettez à jour les slugs de vos anciens médias. <a href="' . esc_url($run_url) . '"><strong>Cliquez ici pour lancer le script par lots.</strong></a> (Sauvegarde recommandée).</p></div>';
        }
    } else {
        run_batch_update_attachment_slugs();
    }
}
add_action('admin_notices', 'show_update_slugs_admin_notice_batch');

/**
 * Gère le processus de mise à jour par lots.
 */
function run_batch_update_attachment_slugs()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $batch_size = 200;
    $current_page = isset($_GET['batch_page']) ? absint($_GET['batch_page']) : 1;

    $query_args = [
        'post_type'      => 'attachment',
        'posts_per_page' => $batch_size,
        'paged'          => $current_page,
        'post_status'    => 'any',
    ];

    $attachments = new WP_Query($query_args);
    $updated_count = 0;

    if ($attachments->have_posts()) {
        while ($attachments->have_posts()) {
            $attachments->the_post();
            $attachment_ID = get_the_ID();
            $current_slug  = get_post()->post_name;
            $id_suffix     = '-' . $attachment_ID;

            if (! str_ends_with($current_slug, $id_suffix)) {
                $new_slug = $current_slug . $id_suffix;
                wp_update_post([ 'ID' => $attachment_ID, 'post_name' => $new_slug ]);
                $updated_count++;
            }
        }
    }

    $total_pages = $attachments->max_num_pages;

    echo '<div class="wrap"><h1>Mise à jour des slugs des médias</h1>';

    if ($current_page <= $total_pages && $total_pages > 0) {
        $next_page = $current_page + 1;
        $next_url = add_query_arg([
            'run_media_rename_batch' => 'true',
            'batch_page' => $next_page,
        ], admin_url('index.php'));

        echo '<p>Traitement du lot ' . $current_page . ' sur ' . $total_pages . '... ' . $updated_count . ' médias mis à jour dans ce lot.</p>';
        echo '<p>Si la page ne se recharge pas automatiquement, <a href="' . esc_url($next_url) . '">cliquez ici pour continuer</a>.</p>';
        echo '<script>setTimeout(function() { window.location.href = "' . $next_url . '"; }, 2000);</script>';
    } else {
        echo '<div class="notice notice-success"><p><strong>Mise à jour terminée !</strong> Tous les slugs ont été traités. Vous pouvez maintenant retirer ce code de votre fichier functions.php.</p></div>';
        update_option('slugs_updated_notice_dismissed', true);
    }

    echo '</div>';

    exit();
}
