<?php

declare(strict_types=1);

if (! function_exists('render_latest_chronicle_promo')) {
    /**
     * Render callback for the "amnesty/latest-chronicle-promo" block.
     * Finds the latest chronicle post and displays its content using another pattern.
     *
     * @return string The HTML output of the block.
     */
    function render_latest_chronicle_promo(): string
    {
        $promo_page_id = get_the_ID();
        $archive_url = '#';

        if ($promo_page_id) {
            $archives_page_query = new WP_Query([
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'name'           => 'archives',
                'post_parent'    => $promo_page_id,
                'posts_per_page' => 1,
                'no_found_rows'  => true,
            ]);

            if ($archives_page_query->have_posts()) {
                while ($archives_page_query->have_posts()) {
                    $archives_page_query->the_post();
                    $archive_url = get_permalink(get_the_ID());
                }

                wp_reset_postdata();
            }
        }

        if (!function_exists('amnesty_get_latest_chronicle_args')) {
            return '<p>Erreur : La fonction de requête des chroniques est manquante.</p>';
        }

        $shared_args = amnesty_get_latest_chronicle_args();

        $args = array_merge($shared_args, [
            'post_type'      => 'chronique',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
        ]);

        $latest_chronicle_query = new WP_Query($args);

        ob_start();

        $found = false;
        $current_year = (int) date('Y');
        $current_month = (int) date('m');

        if ($latest_chronicle_query->have_posts()) {
            while ($latest_chronicle_query->have_posts()) {
                $latest_chronicle_query->the_post();

                $pub_year  = (int) get_post_meta(get_the_ID(), 'publication_year', true);
                $pub_month = (int) get_post_meta(get_the_ID(), 'publication_month', true);

                if ($pub_year > $current_year) {
                    continue;
                }

                if ($pub_year === $current_year && $pub_month > $current_month) {
                    continue;
                }


                get_template_part('patterns/single-chronicle-content', null, [
                    'is_promo_context' => true,
                    'archive_button_url' => $archive_url,
                ]);

                $found = true;
                break;
            }
        }

        if (!$found) {
            echo '<p class="center">Aucun numéro de chronique à afficher.</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }
}
