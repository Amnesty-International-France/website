<?php
/**
 * View: List View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 *
 * @version 4.9.10
 */

global $wpdb;

$current_page   = max( 1, get_query_var( 'paged' ) );
$posts_per_page = (int) tribe_get_option( 'postsPerPage' );
$query_string   = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( $_SERVER['QUERY_STRING'] ) : '';

if ( ! empty( $query_string ) ) {
	$query_string = '?' . $query_string;
}

$query = tribe_get_events(
	[
		'eventDisplay'     => 'custom',
		'posts_per_page'   => $posts_per_page,
		'paged'            => $current_page,
		'suppress_filters' => false,
		'meta_query'       => [
			[
				'key'     => '_EventEndDate',
				'value'   => current_time( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			],
		],
	],
	true
);

$total_pages = $query->max_num_pages;

function get_active_events( $args = [] ) {
	$defaults = [
		'eventDisplay'     => 'custom',
		'posts_per_page'   => -1, // -1 pour tout récupérer
		'suppress_filters' => false,
		'meta_query'       => [
			[
				'key'     => '_EventEndDate',
				'value'   => current_time( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			],
		],
	];

	$args = wp_parse_args( $args, $defaults );

	return tribe_get_events( $args );
}

$count_total_active_events = count( get_active_events() );

$user_longitude = isset( $_GET['lon'] ) ? sanitize_text_field( $_GET['lon'] ) : null;
$user_latitude  = isset( $_GET['lat'] ) ? sanitize_text_field( $_GET['lat'] ) : null;

if ( $user_longitude && $user_latitude ) {
	$current_page = max( 1, get_query_var( 'paged' ) );
	$offset       = ( $current_page - 1 ) * $posts_per_page;

	$total_events = $wpdb->get_results(
		$wpdb->prepare(
			"
    SELECT COUNT(*) FROM (
        SELECT
            post.ID,
            ST_Distance_Sphere(
                POINT(%f, %f),
                POINT(
                    CAST(venue_long.meta_value AS DECIMAL(10,6)),
                    CAST(venue_lat.meta_value AS DECIMAL(10,6))
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
    ) AS total
    ",
			$user_longitude,
			$user_latitude,
		)
	);

	$count_total_active_events = (int) $total_events[0]->{'COUNT(*)'};

	$total_pages = (int) ceil( $count_total_active_events / $posts_per_page );

	[$prev_url, $next_url] = array_map(
		function ( $url ) use ( $user_longitude, $user_latitude ) {
			return $url . ( wp_parse_url( $url, PHP_URL_QUERY ) ? '&' : '?' ) . http_build_query(
				[
					'lon' => $user_longitude,
					'lat' => $user_latitude,
				]
			);
		},
		[ $prev_url, $next_url ]
	);

	if ( 0 === $total_pages ) {
		$next_url = null;
	}

	if ( preg_match( '/page\/(\d+)/', $next_url, $matches ) && $matches[1] > $total_pages ) {
		$next_url = null;
	}
}

if ( preg_match( '/[?&]eventDisplay=past\b/', $prev_url ) ) {
	$prev_url = null;
}

$visible_pagination = $count_total_active_events < $posts_per_page;

function custom_pagination_with_dots( $current_page, $total_pages, $delta = 1 ) {
	$range      = [];
	$pagination = [];

	for ( $i = 1; $i <= $total_pages; $i++ ) {
		if (
			1 === $i ||
			$total_pages === $i ||
			( $i >= $current_page - $delta && $i <= $current_page + $delta )
		) {
			$range[] = $i;
		}
	}

	$last = 0;
	foreach ( $range as $page ) {
		if ( $last && $page - $last > 1 ) {
			$pagination[] = '...';
		}
		$pagination[] = $page;
		$last         = $page;
	}

	return $pagination;
}

$all_pages = custom_pagination_with_dots( $current_page, $total_pages );

?>
<section class="events-pagination <?php echo $visible_pagination ? 'hidden' : ''; ?>">
	<nav
		class="aligncenter section section--small wp-block-query-pagination is-content-justification-space-between is-nowrap is-layout-flex wp-container-core-query-pagination-is-layout-5a589469 wp-block-query-pagination-is-layout-flex"
		role="navigation"
		aria-label="<?php echo esc_attr( __( 'Pagination', 'amnesty' ) ); ?>">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'list/nav/prev', [ 'link' => $prev_url ] );
		} else {
			$this->template( 'list/nav/prev-disabled' );
		}
		?>

		<div class="page-numbers wp-block-query-pagination-numbers">
			<?php foreach ( $all_pages as $p ) : ?>
				<?php if ( '...' === $p ) : ?>
					<span class="page-numbers dots">…</span>
				<?php elseif ( $p === $current_page ) : ?>
					<span aria-current="page"
							class="page-numbers current">
						<?= esc_html( $p ); ?>
					</span>
				<?php else : ?>
					<a class="page-numbers"
						href="<?php echo esc_url( home_url( '/evenements/page/' . (int) $p . '/' . $query_string ) ); ?>">
						<?php echo esc_html( $p ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>

		</div>
		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'list/nav/next', [ 'link' => $next_url ] );
		} else {
			$this->template( 'list/nav/next-disabled' );
		}
		?>
	</nav>
</section>
