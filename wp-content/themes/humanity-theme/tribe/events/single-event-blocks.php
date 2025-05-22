<?php
/**
 * Single Event Template
 *
 * A single event complete template, divided in smaller template parts.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event-blocks.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.7
 */

$event_id = $this->get( 'post_id' );

$is_recurring = '';

if ( ! empty( $event_id ) && function_exists( 'tribe_is_recurring_event' ) ) {
	$is_recurring = tribe_is_recurring_event( $event_id );
}

$main_category = amnesty_get_a_post_term( $event_id );
if ( ! ( $main_category instanceof WP_Term ) ) {
	$main_category = null;
}
?>

<div id="event">
	<div class="current-event">
		<?php if ( $main_category ) : ?>
			<?=
			render_chip_category_block(
				[
					'label' => $main_category->name,
					'link'  => '',
					'size'  => 'large fit-content',
					'style' => 'bg-black',
				]
			);
			?>
		<?php endif; ?>
		<?php $this->template( 'event-single/title' ); ?>
		<?php $this->template( 'single-event/notices' ); ?>
		<?php if ( $is_recurring ) { ?>
			<?php $this->template( 'single-event/recurring-description' ); ?>
		<?php } ?>

		<?php $this->template( 'event-single/content' ); ?>
	</div>
</div>
