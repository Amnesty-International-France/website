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
$current_page   = max( 1, get_query_var( 'paged' ) );
$posts_per_page = tribe_get_option( 'postsPerPage' );

$query = tribe_get_events(
	[
		'eventDisplay'     => 'custom',
		'posts_per_page'   => $posts_per_page,
		'paged'            => $current_page,
		'suppress_filters' => false,
		'meta_query'       => [
			[
				'key'     => '_EventEndDate',
				'value'   => current_time('Y-m-d H:i:s'),
				'compare' => '>=',
				'type'    => 'DATETIME',
			],
		],
	],
	true
);


$total_pages = $query->max_num_pages;

if ( preg_match( '/[?&]eventDisplay=past\b/', $prev_url ) ) {
	$prev_url = null;
}

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
<section class="events-pagination">
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
					<a class="page-numbers" href="<?php echo esc_url( home_url( '/evenements/page/' . (int) $p . '/' ) ); ?>">
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
