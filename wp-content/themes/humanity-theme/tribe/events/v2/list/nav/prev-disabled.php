<?php
/**
 * View: List View Nav Disabled Previous Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.3.0
 */

/* translators: %s: Event (plural or singular). */
$label = sprintf(__('Previous %1$s', 'the-events-calendar'), tribe_get_event_label_plural());

/* translators: %s: Event (plural or singular). */
$events_mobile_friendly_label = sprintf(__('Previous %1$s', 'the-events-calendar'), '<span class="tribe-events-c-nav__prev-label-plural tribe-common-a11y-visual-hide">' . tribe_get_event_label_plural() . '</span>');
?>
<span class="wp-block-query-pagination-previous"><span class="icon"></span>Précédent</span>
