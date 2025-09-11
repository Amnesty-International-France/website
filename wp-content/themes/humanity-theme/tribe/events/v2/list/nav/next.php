<?php
/**
 * View: List View Nav Next Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @var string $link The URL to the next page.
 *
 * @version 5.3.0
 */

/* translators: %s: Event (plural or singular). */
$label = get_next_posts_link('<span class="icon"></span><span>' . __('Next', 'amnesty') . '</span>');

/* translators: %s: Event (plural or singular). */
$events_mobile_friendly_label = get_next_posts_link('<span class="icon"></span><span>' . __('Next', 'amnesty') . '</span>');


?>

<a
	href="<?php echo esc_url(preg_replace('#/liste(/|$)#', '/', $link)); ?>"
	rel="next"
	class="wp-block-query-pagination-next"
>
	Suivant<span class="icon"></span>
</a>
