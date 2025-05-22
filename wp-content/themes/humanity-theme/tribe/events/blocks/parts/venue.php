<?php
/**
 * Venue template part for the Event Venue block
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/venue.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since 6.2.0 Be specific about which venue to render.
 *
 * @var bool $show_map_link Whether to show the map link or not.
 * @var ?int $venue_id The ID of the venue to display.
 */

if ( ! tribe_get_venue_id() ) {
	return;
}
$attributes = $this->get( 'attributes', [] );

$phone   = tribe_get_phone( $venue_id );
$website = tribe_get_venue_website_link( $venue_id );

?>

<div class="event-info">
	<?php if (!empty(tribe_get_city())) : ?>
		<div class="event-info-icon">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M12.2427 11.576L8 15.8187L3.75734 11.576C2.91823 10.7369 2.34679 9.66777 2.11529 8.50389C1.88378 7.34 2.0026 6.13361 2.45673 5.03726C2.91086 3.9409 3.6799 3.00384 4.66659 2.34455C5.65328 1.68527 6.81332 1.33337 8 1.33337C9.18669 1.33337 10.3467 1.68527 11.3334 2.34455C12.3201 3.00384 13.0891 3.9409 13.5433 5.03726C13.9974 6.13361 14.1162 7.34 13.8847 8.50389C13.6532 9.66777 13.0818 10.7369 12.2427 11.576ZM8 8.66665C8.35362 8.66665 8.69276 8.52618 8.94281 8.27613C9.19286 8.02608 9.33334 7.68694 9.33334 7.33332C9.33334 6.9797 9.19286 6.64056 8.94281 6.39051C8.69276 6.14046 8.35362 5.99999 8 5.99999C7.64638 5.99999 7.30724 6.14046 7.05719 6.39051C6.80715 6.64056 6.66667 6.9797 6.66667 7.33332C6.66667 7.68694 6.80715 8.02608 7.05719 8.27613C7.30724 8.52618 7.64638 8.66665 8 8.66665Z"
					fill="#575756"/>
			</svg>
			<p><?php echo tribe_get_city() ?></p>
		</div>
	<?php endif; ?>
	<?php if (!empty(tribe_get_start_time())) : ?>
		<div class="event-info-icon">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd"
					  d="M8.00016 1.33337C11.6822 1.33337 14.6668 4.31804 14.6668 8.00004C14.6668 11.682 11.6822 14.6667 8.00016 14.6667C4.31816 14.6667 1.3335 11.682 1.3335 8.00004C1.3335 4.31804 4.31816 1.33337 8.00016 1.33337ZM8.66683 4.66671H7.3335V9.33337H11.3335V8.00004H8.66683V4.66671Z"
					  fill="#575756"/>
			</svg>
			<p><?php echo tribe_get_start_time() ?></p>
		</div>
	<?php endif; ?>
</div>
