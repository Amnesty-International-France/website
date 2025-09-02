<?php
/**
 * Title: Agenda Content Pattern
 * Description: Agenda content
 * Slug: amnesty/agenda-content
 * Inserter: no
 */

if (!class_exists('Tribe__Events__Main')) {
    return;
}

global $wpdb;

$current_page   = max(1, get_query_var('paged', 1));
$posts_per_page = (int) tribe_get_option('postsPerPage', 10);

$user_longitude = isset($_GET['lon']) ? sanitize_text_field($_GET['lon']) : null;
$user_latitude  = isset($_GET['lat']) ? sanitize_text_field($_GET['lat']) : null;

$offset = ($current_page - 1) * $posts_per_page;
$events = [];
$total_events = 0;

if ($user_longitude && $user_latitude) {
    $total_events = $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT COUNT(post.ID)
            FROM {$wpdb->posts} post
            LEFT JOIN {$wpdb->postmeta} venue_id
                ON venue_id.post_id = post.ID
                AND venue_id.meta_key = '_EventVenueID'
            LEFT JOIN {$wpdb->postmeta} venue_long
                ON venue_long.post_id = venue_id.meta_value
                AND venue_long.meta_key = '_VenueLongitude'
            LEFT JOIN {$wpdb->postmeta} venue_lat
                ON venue_lat.post_id = venue_id.meta_value
                AND venue_lat.meta_key = '_VenueLatitude'
            LEFT JOIN {$wpdb->postmeta} event_national
                ON event_national.post_id = post.ID
                AND event_national.meta_key = '_EventNational'
            WHERE post.post_type = 'tribe_events'
            AND post.post_status = 'publish'
            AND (
                SELECT meta_value
                FROM {$wpdb->postmeta}
                WHERE post_id = post.ID
                  AND meta_key = '_EventEndDate'
                LIMIT 1
            ) >= NOW()
            AND (
                (ST_Distance_Sphere(
                    ST_SRID(POINT(%f, %f), 4326),
                    ST_SRID(
                        POINT(
                            CAST(venue_long.meta_value AS DECIMAL(10,6)),
                            CAST(venue_lat.meta_value AS DECIMAL(10,6))
                        ), 4326
                    )
                ) <= 100000) OR event_national.meta_value = 1
            )
            ",
            $user_longitude,
            $user_latitude
        )
    );

    $events = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT *,
            ST_Distance_Sphere(
                ST_SRID(POINT(%f, %f), 4326),
                ST_SRID(
                    POINT(
                        CAST(venue_long.meta_value AS DECIMAL(10,6)),
                        CAST(venue_lat.meta_value AS DECIMAL(10,6))
                    ), 4326
                )
            ) AS distance,
            event_national.meta_value AS national_event
            FROM {$wpdb->posts} post
            LEFT JOIN {$wpdb->postmeta} venue_id
                ON venue_id.post_id = post.ID
                AND venue_id.meta_key = '_EventVenueID'
            LEFT JOIN {$wpdb->postmeta} venue_long
                ON venue_long.post_id = venue_id.meta_value
                AND venue_long.meta_key = '_VenueLongitude'
            LEFT JOIN {$wpdb->postmeta} venue_lat
                ON venue_lat.post_id = venue_id.meta_value
                AND venue_lat.meta_key = '_VenueLatitude'
            LEFT JOIN {$wpdb->postmeta} event_national
                ON event_national.post_id = post.ID
                AND event_national.meta_key = '_EventNational'
            WHERE post.post_type = 'tribe_events'
            AND post.post_status = 'publish'
            AND (
                SELECT meta_value
                FROM {$wpdb->postmeta}
                WHERE post_id = post.ID
                  AND meta_key = '_EventEndDate'
                LIMIT 1
            ) >= NOW()
            HAVING distance <= 100000 OR event_national.meta_value = 1
            ORDER BY distance ASC,
                (
                    SELECT meta_value
                    FROM {$wpdb->postmeta}
                    WHERE post_id = post.ID
                      AND meta_key = '_EventStartDate'
                    LIMIT 1
                ) ASC
            LIMIT %d OFFSET %d
            ",
            $user_longitude,
            $user_latitude,
            $posts_per_page,
            $offset
        )
    );
} else {
    $query = new WP_Query([
        'post_type'      => 'tribe_events',
        'posts_per_page' => $posts_per_page,
        'paged'          => $current_page,
        'eventDisplay'   => 'list',
        'start_date'     => 'now',
    ]);
    $events = $query->posts;
    $total_events = $query->found_posts;
}
?>

<!-- wp:pattern {"slug":"amnesty/my-space-sidebar"} /-->
<main class="aif-donor-space-content">
    <!-- wp:pattern {"slug":"amnesty/my-space-header"} /-->
    <div class="aif-agenda">
        <div class="events wp-site-blocks">
            <div class="event-filters">
                <div class="event-filters-container">
                    <a class="filter-button" href="<?php echo esc_url(get_permalink()); ?>">
                        Tous les évènements
                    </a>
                    <div class="event-filters-search">
                        <div class="event-filters-form">
                            <form class="form-location" action="<?php echo esc_url(get_permalink()); ?>" method="get">
                                <label for="input-localisation" class="sr-only">Ville ou code postal</label>
                                <input
                                    id="input-localisation"
                                    name="location"
                                    type="text"
                                    placeholder="Ville ou code postal"
                                >
                                <button type="submit" class="filter-button">
                                    <?php echo file_get_contents(get_template_directory() . '/assets/images/icon-search.svg'); ?>
                                </button>
                            </form>

                            <span>ou</span>
                            <button id="localisation" class="btn btn--yellow">Me Géolocaliser</button>
                        </div>
                        <div class="event-filters-results hidden">
                            <ul class="search-results"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="events-list">
                <?php if (!empty($events)) : ?>
                    <section class="events-list-container grid-three-columns">
                        <?php foreach ($events as $event) : ?>
                            <?php
                            echo render_block([
                                'blockName'   => 'amnesty-core/event-card',
                                'attrs'       => ['postId' => $event->ID],
                                'innerBlocks' => [],
                            ]);
                            ?>
                        <?php endforeach; ?>
                    </section>

                    <?php
                    $total_pages = ceil($total_events / $posts_per_page);

                    if ($total_pages > 1) :
                        if (!function_exists('custom_pagination_with_dots')) {
                            function custom_pagination_with_dots($current_page, $total_pages, $delta = 1)
                            {
                                $range      = [];
                                $pagination = [];

                                for ($i = 1; $i <= $total_pages; $i++) {
                                    if (
                                        1 === $i ||
                                        $total_pages === $i ||
                                        ($i >= $current_page - $delta && $i <= $current_page + $delta)
                                    ) {
                                        $range[] = $i;
                                    }
                                }

                                $last = 0;
                                foreach ($range as $page) {
                                    if ($last && $page - $last > 1) {
                                        $pagination[] = '...';
                                    }
                                    $pagination[] = $page;
                                    $last         = $page;
                                }

                                return $pagination;
                            }
                        }

                        $all_pages = custom_pagination_with_dots($current_page, $total_pages);
                        $prev_url = ($current_page > 1) ? get_pagenum_link($current_page - 1) : '';
                        $next_url = ($current_page < $total_pages) ? get_pagenum_link($current_page + 1) : '';
                        ?>
                        <section class="events-pagination">
                            <nav class="aligncenter section section--small wp-block-query-pagination is-content-justification-space-between is-nowrap is-layout-flex wp-container-core-query-pagination-is-layout-5a589469 wp-block-query-pagination-is-layout-flex" role="navigation" aria-label="<?php echo esc_attr(__('Pagination', 'amnesty')); ?>">
                                <?php if (!empty($prev_url)) : ?>
                                    <a href="<?php echo esc_url($prev_url); ?>" class="wp-block-query-pagination-previous">
                                        <?php echo esc_html__('« Précédent', 'amnesty'); ?>
                                    </a>
                                <?php else : ?>
                                    <span class="wp-block-query-pagination-previous is-disabled" aria-hidden="true"><?php echo esc_html__('« Précédent', 'amnesty'); ?></span>
                                <?php endif; ?>

                                <div class="page-numbers wp-block-query-pagination-numbers">
                                    <?php foreach ($all_pages as $p) : ?>
                                        <?php if ('...' === $p) : ?>
                                            <span class="page-numbers dots">…</span>
                                        <?php elseif ($p === $current_page) : ?>
                                            <span aria-current="page" class="page-numbers current">
                                                <?php echo esc_html($p); ?>
                                            </span>
                                        <?php else : ?>
                                            <a class="page-numbers" href="<?php echo esc_url(get_pagenum_link($p)); ?>">
                                                <?php echo esc_html($p); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!empty($next_url)) : ?>
                                    <a href="<?php echo esc_url($next_url); ?>" class="wp-block-query-pagination-next">
                                        <?php echo esc_html__('Suivant »', 'amnesty'); ?>
                                    </a>
                                <?php else : ?>
                                     <span class="wp-block-query-pagination-next is-disabled" aria-hidden="true"><?php echo esc_html__('Suivant »', 'amnesty'); ?></span>
                                <?php endif; ?>
                            </nav>
                        </section>
                    <?php
                    endif;
                    ?>

                <?php else : ?>
                    <p class="no-events">Désolé, il n'y a aucun résultat pour cette recherche.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
