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

?>
<div class="events wp-site-blocks">
	<?php
	echo do_blocks( WP_Block_Patterns_Registry::get_instance()->get_registered( 'amnesty/archive-hero' )['content'] );
	?>
	<div class="event-filters">
		<div class="event-filters-container">
			<a class="filter-button" href="<?php echo esc_url( tribe_get_events_link() ); ?>">
				Tous les évènements
			</a>
			<div class="event-filters-search">
				<div class="event-filters-form">
					<form class="form-location" action="">
						<label for="input-localisation"></label>
						<input id="input-localisation" name="location" type="text" placeholder="Code postal ou ville">
						<button class="filter-button">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
								xmlns="http://www.w3.org/2000/svg">
								<path
									d="M21.5 23.25L13.625 15.375C13 15.875 12.2813 16.2708 11.4688 16.5625C10.6563 16.8542 9.79167 17 8.875 17C6.60417 17 4.6825 16.2133 3.11 14.64C1.5375 13.0667 0.750834 11.145 0.750001 8.875C0.749167 6.605 1.53583 4.68333 3.11 3.11C4.68417 1.53667 6.60583 0.75 8.875 0.75C11.1442 0.75 13.0663 1.53667 14.6413 3.11C16.2163 4.68333 17.0025 6.605 17 8.875C17 9.79167 16.8542 10.6562 16.5625 11.4688C16.2708 12.2812 15.875 13 15.375 13.625L23.25 21.5L21.5 23.25ZM8.875 14.5C10.4375 14.5 11.7658 13.9533 12.86 12.86C13.9542 11.7667 14.5008 10.4383 14.5 8.875C14.4992 7.31167 13.9525 5.98375 12.86 4.89125C11.7675 3.79875 10.4392 3.25167 8.875 3.25C7.31083 3.24833 5.98292 3.79542 4.89125 4.89125C3.79958 5.98708 3.2525 7.315 3.25 8.875C3.2475 10.435 3.79458 11.7633 4.89125 12.86C5.98792 13.9567 7.31583 14.5033 8.875 14.5Z"
									fill="#FFFF00"/>
							</svg>
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
			<?php foreach ( $events as $event ) : ?>
				<?php
				$block = [
					'blockName'   => 'amnesty-core/event-card',
					'attrs'       => [ 'postId' => $event->ID ],
					'innerBlocks' => [],
				];
				echo render_block( $block );
				?>
			<?php endforeach; ?>
		</section>
	</div>
</div>

<?php $this->template( 'components/breakpoints' ); ?>
