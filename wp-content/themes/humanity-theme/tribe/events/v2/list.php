<?php
/**
 * View: List View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @since 6.1.4 Changing our nonce verification structures.
 * @since 6.12.0 Add aria-label to events list for improved accessibility.
 *
 * @version 6.2.0
 * @since 6.2.0 Moved the header information into a new components/header.php template.
 *
 * @var array $events The array containing the events.
 * @var string $rest_url The REST URL.
 * @var string $rest_method The HTTP method, either `POST` or `GET`, the View will use to make requests.
 * @var int $should_manage_url int containing if it should manage the URL.
 * @var bool $disable_event_search Boolean on whether to disable the event search.
 * @var string[] $container_classes Classes used for the container of the view.
 * @var array $container_data An additional set of container `data` attributes.
 * @var string $breakpoint_pointer String we use as pointer to the current view we are setting up with breakpoints.
 */

global $wpdb;

$current_page = max(1, get_query_var('paged'));

$user_longitude = isset($_GET['lon']) ? sanitize_text_field($_GET['lon']) : null;
$user_latitude = isset($_GET['lat']) ? sanitize_text_field($_GET['lat']) : null;

if ($user_longitude && $user_latitude) {
	$posts_per_page = (int)tribe_get_option('postsPerPage');

	$current_page = max(1, get_query_var('paged'));
	$offset = ($current_page - 1) * $posts_per_page;

	$events = $wpdb->get_results(
		$wpdb->prepare(
			"
    SELECT
		*,
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
			tribe_get_option('postsPerPage'),
			$offset
		)
	);
}

?>
<div class="events wp-site-blocks">
	<?php
	echo do_blocks(WP_Block_Patterns_Registry::get_instance()->get_registered('amnesty/archive-hero')['content']);
	?>
	<div class="event-filters">
		<div class="event-filters-container">
			<a class="filter-button" href="<?php echo esc_url(tribe_get_events_link()); ?>">
				Tous les évènements
			</a>
			<div class="event-filters-search">
				<div class="event-filters-form">
					<form class="form-location" action="">
						<label for="input-localisation"></label>
						<input id="input-localisation" name="location" type="text" placeholder="Ville ou code postal">
						<button class="filter-button">
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
		<?php if (\count($events) === 0) : ?>
			<p class="no-events"> Désolé, il n'y a aucun résultat pour cette recherche.</p>
		<?php else : ?>
			<section class="events-list-container grid-three-columns">
				<?php foreach ($events as $event) : ?>
					<?php
					echo render_block(
						[
							'blockName' => 'amnesty-core/event-card',
							'attrs' => ['postId' => $event->ID],
							'innerBlocks' => [],
						]
					);
					?>
				<?php endforeach; ?>
			</section>
			<?php $this->template('list/nav'); ?>
		<?php endif; ?>
	</div>
</div>

