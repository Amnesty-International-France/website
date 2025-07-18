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
    function render_agenda_homepage_block(array $attributes): string {
        $selection_mode     = $attributes['selectionMode'] ?? 'latest';
        $selected_event_ids = $attributes['selectedEventIds'] ?? [];
        $chronicle_image_url = $attributes['chronicleImageUrl'] ?? '';

        $events_args = [];

        if ($selection_mode === 'manual' && !empty($selected_event_ids)) {
            $events_args = [
                'post_type'      => 'tribe_events',
                'posts_per_page' => count($selected_event_ids),
                'post__in'       => array_map('intval', $selected_event_ids),
            ];
        } else {
            $events_args = [
                'post_type'      => 'tribe_events',
                'posts_per_page' => 2,
                'eventDisplay'   => 'upcoming',
                'orderby'        => 'meta_value',
                'meta_key'       => '_EventStartDate',
                'order'          => 'ASC',
                'meta_query'     => [
                    [
                        'key'     => '_EventStartDate',
                        'value'   => date('Y-m-d H:i:s'),
                        'compare' => '>=',
                        'type'    => 'DATETIME',
                    ],
                ],
            ];
        }

        $events_query = new WP_Query($events_args);

        ob_start();
        ?>
        <section class="agenda-chronicle-homepage">
            <div class="agenda-homepage">
                <h2 class="agenda-homepage-title">Agenda</h2>
                <div class="agenda-homepage-events">
                    <?php if ($events_query->have_posts()) : ?>
                        <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                            <?php
                            $event_id = get_the_ID();

                            $event_card_block_attrs = [
                                'postId' => $event_id,
                                'direction' => 'landscape',
                            ];

                            $event_card_block = [
                                'blockName'   => 'amnesty-core/event-card',
                                'attrs'       => $event_card_block_attrs,
                                'innerBlocks' => [],
                            ];

                            echo render_block($event_card_block);
                            ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p><?php esc_html_e('Aucun événement à venir trouvé.', 'amnesty-core'); ?></p>
                    <?php endif; ?>
                </div>
                <div class='custom-button-block left'>
                    <a href="/evenements" target="_blank" rel="noopener noreferrer" class="custom-button">
                        <div class='content outline-black medium'>
                            <div class="icon-container">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
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
                            <a href="/magazine-la-chronique" target="_blank" rel="noopener noreferrer" class="custom-button">
                                <div class='content bg-yellow medium'>
                                    <div class="icon-container">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            strokeWidth="1.5"
                                            stroke="currentColor"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
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
