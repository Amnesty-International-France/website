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

$userLongitude = isset($_GET['lon']) ? sanitize_text_field($_GET['lon']) : null;
$userLatitude = isset($_GET['lat']) ? sanitize_text_field($_GET['lat']) : null;

if ($userLongitude && $userLatitude) {
	$events = $wpdb->get_results($wpdb->prepare("
    SELECT
		*,
        ST_Distance_Sphere(
		  POINT(%f, %f),
		  POINT(
			(
			  SELECT pm1.meta_value
			  FROM {$wpdb->postmeta} pm1
			  WHERE pm1.post_id = (
				SELECT pm_event.meta_value
				FROM {$wpdb->postmeta} pm_event
				WHERE pm_event.post_id = post.ID
				  AND pm_event.meta_key = '_EventVenueID'
				LIMIT 1
			  )
			  AND pm1.meta_key = '_VenueLongitude'
			  LIMIT 1
			),
			(
			  SELECT pm2.meta_value
			  FROM {$wpdb->postmeta} pm2
			  WHERE pm2.post_id = (
				SELECT pm_event.meta_value
				FROM {$wpdb->postmeta} pm_event
				WHERE pm_event.post_id = post.ID
				  AND pm_event.meta_key = '_EventVenueID'
				LIMIT 1
			  )
			  AND pm2.meta_key = '_VenueLatitude'
			  LIMIT 1
			)
		  )
		) AS distance
    FROM {$wpdb->posts} post
    WHERE post_type = 'tribe_events'
    AND post_status = 'publish'
    ORDER BY (distance IS NULL) ASC, distance ASC
", $userLongitude, $userLatitude));
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
						<input id="input-localisation" name="location" type="text" placeholder="Code postal ou ville">
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
		<section class="events-list-container grid-three-columns">
			<?php foreach ($events as $event) : ?>
				<?php
				$block = [
					'blockName' => 'amnesty-core/event-card',
					'attrs' => ['postId' => $event->ID],
					'innerBlocks' => [],
				];
				echo render_block($block);
				?>
			<?php endforeach; ?>
		</section>
	</div>
</div>

<?php $this->template('components/breakpoints'); ?>
