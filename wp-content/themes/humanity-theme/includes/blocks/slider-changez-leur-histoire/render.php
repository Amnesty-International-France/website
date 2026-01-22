<?php

declare(strict_types=1);

if (!function_exists('render_slider_changez_leur_histoire_block')) {
    /**
     * Render the "Slider Changez leur histoire" block.
     *
     * @param array<string, mixed> $attributes Block attributes.
     * @return string HTML output.
     */
    function render_slider_changez_leur_histoire_block(array $attributes): string
    {
        $selected_posts = $attributes['selectedPosts'] ?? [];

        if (empty($selected_posts)) {
            return '';
        }

        $post_ids = array_filter(array_map('absint', wp_list_pluck($selected_posts, 'id')));
        $post_ids = array_values(array_unique($post_ids));

        if (count($post_ids) < 4 || count($post_ids) > 20) {
            return '';
        }

        $query_args = [
            'post_type'      => 'petition',
            'post__in'       => $post_ids,
            'posts_per_page' => count($post_ids),
            'orderby'        => 'post__in',
        ];
        $slider_query = new WP_Query($query_args);

        if (!$slider_query->have_posts() || $slider_query->post_count < 4) {
            return '';
        }

        ob_start();
        ?>
        <div class="changez-leur-histoire-slider-block">
            <div class="changez-leur-histoire-slider-wrapper">
                <div class="slider-nav prev">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <path d="M11 6L21 16L11 26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="swiper">
                    <div class="swiper-wrapper">
                        <?php while ($slider_query->have_posts()): $slider_query->the_post(); ?>
                            <?php $post_id = get_the_ID(); ?>
                            <div class="swiper-slide">
                                <?php
                                $petition_type = get_field('type', $post_id);
                            if (is_array($petition_type)) {
                                $petition_type = $petition_type['value'] ?? '';
                            }

                            $template_path = $petition_type === 'action-soutien'
                                ? locate_template('partials/action-card-change-their-history.php')
                                : locate_template('partials/petition-card-change-their-history.php');
                            if ($template_path) {
                                $args = [
                                    'post_id' => $post_id,
                                    'direction' => 'portrait',
                                ];
                                include $template_path;
                            }
                            ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="slider-nav next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <path d="M11 6L21 16L11 26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
}
