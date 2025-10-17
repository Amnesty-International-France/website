<?php
/**
 * Server-side rendering for the Agenda Homepage block.
 *
 * @param array<string, mixed> $attributes The block attributes.
 *
 * @return string The rendered block HTML.
 */
declare(strict_types=1);

if (!function_exists('render_agenda_homepage_block')) {
    function render_agenda_homepage_block(array $attributes): string
    {
        $selection_mode      = $attributes['selectionMode'] ?? 'latest';
        $selected_event_ids  = $attributes['selectedEventIds'] ?? [];
        $chronicle_image_url = $attributes['chronicleImageUrl'] ?? '';

        ob_start();
        ?>
        <section class="agenda-chronicle-homepage">
            <div class="agenda-homepage">
                <h2 class="agenda-homepage-title">Agenda</h2>
                <div class="agenda-homepage-events">
                    <?php
                    if ($selection_mode === 'manual' && !empty($selected_event_ids)) {
                        $events_args = [
                            'post_type'      => 'tribe_events',
                            'posts_per_page' => count($selected_event_ids),
                            'post__in'       => array_map('intval', $selected_event_ids),
                            'ignore_sticky_posts' => 1, // Bonne pratique
                        ];
                        $events_query = new WP_Query($events_args);

                        $posts_by_id = [];
                        if ($events_query->have_posts()) {
                            while ($events_query->have_posts()) {
                                $events_query->the_post();
                                $posts_by_id[get_the_ID()] = $events_query->post;
                            }
                        }
                        wp_reset_postdata();

                        $sorted_posts = [];
                        foreach ($selected_event_ids as $id) {
                            $id = intval($id);
                            if (isset($posts_by_id[$id])) {
                                $sorted_posts[] = $posts_by_id[$id];
                            }
                        }

                        if (!empty($sorted_posts)) {
                            global $post;
                            foreach ($sorted_posts as $post) {
                                setup_postdata($post);

                                $event_card_block = [
                                    'blockName'   => 'amnesty-core/event-card',
                                    'attrs'       => [
                                        'postId'    => get_the_ID(),
                                        'direction' => 'landscape',
                                    ],
                                ];
                                echo render_block($event_card_block);
                            }
                            wp_reset_postdata();
                        }

                    } else {
                        $events_args = [
                            'post_type'      => 'tribe_events',
                            'posts_per_page' => 2,
                            'eventDisplay'   => 'upcoming',
                            'orderby'        => 'event_date',
                            'order'          => 'ASC',
                        ];
                        $events_query = new WP_Query($events_args);

                        if ($events_query->have_posts()) {
                            while ($events_query->have_posts()) {
                                $events_query->the_post();
                                $event_card_block = [
                                    'blockName'   => 'amnesty-core/event-card',
                                    'attrs'       => ['postId' => get_the_ID(), 'direction' => 'landscape'],
                                ];
                                echo render_block($event_card_block);
                            }
                        } else {
                            echo '<p>' . esc_html__('Aucun événement à venir trouvé.', 'amnesty-core') . '</p>';
                        }
                        wp_reset_postdata();
                    }
        ?>
                </div>
                <div class='custom-button-block left'>
                     <a href="/evenements" class="custom-button">
                        <div class='content outline-black medium'>
                            <div class="icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </div>
                            <div class="button-label">Voir les événements près de chez moi</div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="chronicle-homepage">
                <h2 class="chronicle-homepage-title">A découvrir</h2>
                <div class="chronicle-card">
                    <div class="chronicle-card-image-container">
                        <?php if (!empty($chronicle_image_url)) : ?>
                            <img src="<?php echo esc_url($chronicle_image_url); ?>" class="chronicle-card-image" alt="<?php esc_attr_e('Image de La Chronique', 'amnesty-core'); ?>" />
                        <?php endif; ?>
                    </div>
                    <div class="chronicle-card-content">
                        <h3 class="chronicle-card-title">La chronique</h3>
                        <p class="chronicle-card-chapo">Le magazine des droits humains</p>
                        <div class='custom-button-block center'>
                            <a href="https://soutenir.amnesty.fr/b?cid=365&lang=fr_FR" class="custom-button">
                                <div class='content bg-yellow medium'>
                                    <div class="icon-container">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                    </div>
                                    <div class="button-label">Abonnez-vous pour 3€/mois</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
