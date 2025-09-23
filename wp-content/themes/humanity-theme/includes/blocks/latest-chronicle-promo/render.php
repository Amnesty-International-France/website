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

        $args = [
            'post_type'      => 'chronique',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ];

        $latest_chronicle_query = new WP_Query($args);

        ob_start();

        if ($latest_chronicle_query->have_posts()) {
            while ($latest_chronicle_query->have_posts()) {
                $latest_chronicle_query->the_post();

                get_template_part('patterns/single-chronicle-content', null, [
                    'is_promo_context' => true,
                    'archive_button_url' => $archive_url,
                ]);
            }
        } else {
            echo '<p class="center">Aucun numéro de chronique à afficher.</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }
}
