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
                ]);
            }
        } else {
            echo '<p>Aucun numéro de chronique à afficher.</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }
}
