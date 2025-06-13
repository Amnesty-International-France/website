<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 */

if (!defined('ABSPATH')) {
	die('-1');
}


$events_label_singular = tribe_get_event_label_singular();
$events_label_plural = tribe_get_event_label_plural();

$event_id = Tribe__Events__Main::postIdHelper(get_the_ID());

/**
 * Allows filtering of the event ID.
 *
 * @param numeric $event_id
 * @since 6.0.1
 *
 */
$event_id = apply_filters('tec_events_single_event_id', $event_id);

/**
 * Allows filtering of the single event template title classes.
 *
 * @param array $title_classes List of classes to create the class string from.
 * @param numeric $event_id The ID of the displayed event.
 * @since 5.8.0
 *
 */
$title_classes = apply_filters('tribe_events_single_event_title_classes', ['tribe-events-single-event-title'], $event_id);
$title_classes = implode(' ', tribe_get_classes($title_classes));

/**
 * Allows filtering of the single event template title before HTML.
 *
 * @param string $before HTML string to display before the title text.
 * @param numeric $event_id The ID of the displayed event.
 * @since 5.8.0
 *
 */
$before = apply_filters('tribe_events_single_event_title_html_before', '<h1 class="event-title">', $event_id);

/**
 * Allows filtering of the single event template title after HTML.
 *
 * @param string $after HTML string to display after the title text.
 * @param numeric $event_id The ID of the displayed event.
 * @since 5.8.0
 *
 */
$after = apply_filters('tribe_events_single_event_title_html_after', '</h1>', $event_id);

/**
 * Allows filtering of the single event template title HTML.
 *
 * @param string $after HTML string to display. Return an empty string to not display the title.
 * @param numeric $event_id The ID of the displayed event.
 * @since 5.8.0
 *
 */
$title = apply_filters('tribe_events_single_event_title_html', the_title($before, $after, false), $event_id);
$cost = tribe_get_formatted_cost($event_id);
$event = tribe_get_event($event_id);

$main_category = amnesty_get_a_post_term($event_id);
if (!($main_category instanceof WP_Term)) {
	$main_category = null;
}

$post_type = get_post_type($event);
$chip_style = 'bg-yellow';

$post_type_object = get_post_type_object($post_type);
$main_category = $post_type_object->name === 'tribe_events' ? $post_type_object->label : 'Évènement';

?>

<div class="event">
	<div class="current-event">
		<?php if ($main_category) : ?>
			<?=
			render_chip_category_block(
				[
					'label' => $main_category,
					'link' => '',
					'size' => 'large fit-content',
					'style' => 'bg-black',
				]
			);
			?>
		<?php endif; ?>

		<?php tribe_the_notices() ?>

		<?php echo $title; ?>

		<div class="event-details">
			<div class="event-date">
				<div class="event-info-icon">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd"
							  d="M1.3335 7.33329H14.6668V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V7.33329ZM11.3335 1.99996H14.0002C14.3684 1.99996 14.6668 2.29844 14.6668 2.66663V5.99996H1.3335V2.66663C1.3335 2.29844 1.63197 1.99996 2.00016 1.99996H4.66683V0.666626H6.00016V1.99996H10.0002V0.666626H11.3335V1.99996Z"
							  fill="#575756"/>
					</svg>

					<?php if (tribe_get_start_date($event_id, false, 'd M Y') === tribe_get_end_date($event_id, false, 'd M Y')) : ?>
						<p>Du <?php echo tribe_get_start_date($event_id, false, 'd M Y'); ?>
							au <?php echo tribe_get_end_date($event_id, false, 'd M Y'); ?>
						</p>
					<?php else: ?>
						<p>Le <?php echo tribe_get_start_date($event_id, false, 'd M Y'); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="event-info">
				<?php if (!empty(tribe_get_city($event_id))) : ?>
					<div class="event-info-icon">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path
								d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z"
								fill="#575756"/>
						</svg>
						<p><?php echo tribe_get_city($event_id) ?></p>
					</div>
				<?php endif; ?>
				<?php if (!empty(tribe_get_start_time($event_id))) : ?>
					<div class="event-info-icon">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd"
								  d="M8.00016 1.33337C11.6822 1.33337 14.6668 4.31804 14.6668 8.00004C14.6668 11.682 11.6822 14.6667 8.00016 14.6667C4.31816 14.6667 1.3335 11.682 1.3335 8.00004C1.3335 4.31804 4.31816 1.33337 8.00016 1.33337ZM8.66683 4.66671H7.3335V9.33337H11.3335V8.00004H8.66683V4.66671Z"
								  fill="#575756"/>
						</svg>
						<p><?php echo tribe_get_start_time($event_id) ?></p>
					</div>
				<?php endif; ?>
				<?php if (!empty(tribe_get_organizer_email($event_id))) : ?>
				<div class="event-info-icon">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd"
							  d="M2.00016 2H14.0002C14.3684 2 14.6668 2.29848 14.6668 2.66667V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V2.66667C1.3335 2.29848 1.63197 2 2.00016 2ZM8.04016 7.78867L3.7655 4.15867L2.90216 5.17467L8.04883 9.54467L13.1028 5.17133L12.2308 4.16267L8.04016 7.78867Z"
							  fill="#575756"/>
					</svg>
					<p><?php echo tribe_get_organizer_email($event_id) ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php while (have_posts()) : the_post(); ?>
			<div id="post-<?php the_ID(); ?>" class="event-content">
				<!-- Event featured image, but exclude link -->
				<?php echo tribe_event_featured_image($event_id, 'full', false); ?>

				<?php echo tribe_get_the_content() ?>
			</div>
			<?php if (get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option('showComments', false)) comments_template() ?>
		<?php endwhile; ?>
	</div>
	<div class="event-near">
		<h3>Près de chez vous</h3>
		<h5>Trouvez d’autres événements pour agir avec nous</h5>
		<button id="localisation">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd"
					  d="M16.172 11L10.808 5.636L12.222 4.222L20 12L12.222 19.778L10.808 18.364L16.172 13H4V11H16.172Z"
					  fill="black"/>
			</svg>
			rechercher
		</button>
	</div>

	<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
	<footer class="wp-block-group article-footer">
		<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
	</footer>
	<!-- /wp:group -->
</div>

