<?php
/**
 * Block: Event Date Time
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-datetime.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.1
 *
 */

$event_id = get_the_ID();
$event = get_post($event_id);

/**
 * If a yearless date format should be preferred.
 *
 * By default, this will be true if the event starts and ends in the current year.
 *
 * @param bool $use_yearless_format
 * @param WP_Post $event
 * @since 0.2.5-alpha
 *
 */
$use_yearless_format = apply_filters('tribe_events_event_block_datetime_use_yearless_format',
	(
		tribe_get_start_date($event_id, false, 'Y') === date_i18n('Y')
		&& tribe_get_end_date($event_id, false, 'Y') === date_i18n('Y')
	),
	$event
);

$time_format = tribe_get_time_format();
$date_format = tribe_get_date_format(!$use_yearless_format);
$timezone = get_post_meta($event_id, '_EventTimezone', true);
$show_time_zone = $this->attr('showTimeZone');
$local_start_time = tribe_get_start_date($event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT);
$time_zone_label = $this->attr('timeZoneLabel');

if (is_null($show_time_zone)) {
	$show_time_zone = tribe_get_option('tribe_events_timezones_show_zone', false);
}

if (is_null($time_zone_label)) {
	$time_zone_label = Tribe__Events__Timezones::is_mode('site') ? Tribe__Events__Timezones::wp_timezone_abbr($local_start_time) : Tribe__Events__Timezones::get_event_timezone_abbr($event_id);
}

$formatted_start_date = tribe_get_start_date($event_id, false, 'd M Y');
$formatted_start_time = tribe_get_start_time($event_id, $time_format);
$formatted_end_date = tribe_get_end_date($event_id, false, 'd M Y');
$formatted_end_time = tribe_get_end_time($event_id, $time_format);
$separator_date = get_post_meta($event_id, '_EventDateTimeSeparator', true);
$separator_time = get_post_meta($event_id, '_EventTimeRangeSeparator', true);

if (empty($separator_time)) {
	$separator_time = tec_events_get_time_range_separator();
}
if (empty($separator_date)) {
	$separator_date = tec_events_get_date_time_separator();
}

$is_all_day = tribe_event_is_all_day($event_id);
$is_same_day = $formatted_start_date == $formatted_end_date;
$is_same_start_end = $formatted_start_date == $formatted_end_date && $formatted_start_time == $formatted_end_time;

$event_id = $this->get('post_id');

$default_classes = ['tribe-events-schedule', 'tribe-clearfix'];

// Add the custom classes from the block attributes.
$classes = isset($attributes['className']) ? array_merge($default_classes, [$attributes['className']]) : $default_classes;
?>
<div class="event-date">
	<div class="event-info-icon">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd"
				  d="M1.3335 7.33329H14.6668V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V7.33329ZM11.3335 1.99996H14.0002C14.3684 1.99996 14.6668 2.29844 14.6668 2.66663V5.99996H1.3335V2.66663C1.3335 2.29844 1.63197 1.99996 2.00016 1.99996H4.66683V0.666626H6.00016V1.99996H10.0002V0.666626H11.3335V1.99996Z"
				  fill="#575756"/>
		</svg>
		<p>
			<?php echo esc_html($formatted_start_date); ?>
			<?php if (!$is_all_day) : ?>
				<?php echo esc_html($separator_date); ?> DATE  1
				<?php echo esc_html($formatted_start_time); ?>
			<?php elseif ($is_same_day) : ?>
				<?php echo esc_html__('Toute la journée', 'the-events-calendar'); ?>
			<?php endif; ?>

			<?php if (!$is_same_start_end) : ?>
				<?php if (!$is_all_day || !$is_same_day) : ?>
					au
				<?php endif; ?>

				<?php if (!$is_same_day) : ?>
					<?php echo esc_html($formatted_end_date); ?>
					<?php if (!$is_all_day) : ?>
						<?php echo esc_html($separator_date); ?> DATE 2

						<?php echo esc_html($formatted_end_time); ?>
					<?php endif; ?>

				<?php elseif (!$is_all_day) : ?>
					<?php echo esc_html($formatted_end_time); ?>
				<?php endif; ?>
			<?php endif; ?>
		</p>
	</div>
</div>
