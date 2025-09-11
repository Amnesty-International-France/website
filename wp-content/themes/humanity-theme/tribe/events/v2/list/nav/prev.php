<?php
/**
 * View: List View Nav Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the previous page.
 *
 * @version 5.3.0
 */

/* translators: %s: Event (plural or singular). */
$label = get_previous_posts_link('<span class="icon"></span><span>' . __('Previous', 'amnesty') . '</span>');

/* translators: %s: Event (plural or singular). */
$events_mobile_friendly_label = get_previous_posts_link('<span class="icon"></span><span>' . __('Previous', 'amnesty') . '</span>');
?>
<a
	href="<?php echo esc_url(preg_replace('#/liste(/|$)#', '/', $link)); ?>"
	rel="prev"
	class="wp-block-query-pagination-previous"
>
	<span class="icon"></span>Précédent
</a>
