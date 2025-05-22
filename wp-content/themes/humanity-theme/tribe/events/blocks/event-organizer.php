<?php
/**
 * Block: Event Organizer
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-organizer.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.1
 *
 */

$organizer = $this->attr( 'organizer' );

if ( ! $organizer ) {
	return;
}

$phone   = tribe_get_organizer_phone( $organizer );
$website = tribe_get_organizer_website_link( $organizer );
$email   = tribe_get_organizer_email( $organizer );

$default_classes = [ 'tribe-block', 'tribe-block__organizer__details', 'tribe-clearfix' ];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>

<?php if ($email) : ?>
	<div class="event-info-icon">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd"
				  d="M2.00016 2H14.0002C14.3684 2 14.6668 2.29848 14.6668 2.66667V13.3333C14.6668 13.7015 14.3684 14 14.0002 14H2.00016C1.63197 14 1.3335 13.7015 1.3335 13.3333V2.66667C1.3335 2.29848 1.63197 2 2.00016 2ZM8.04016 7.78867L3.7655 4.15867L2.90216 5.17467L8.04883 9.54467L13.1028 5.17133L12.2308 4.16267L8.04016 7.78867Z"
				  fill="#575756"/>
		</svg>
		<p><?php echo $email ?></p>
	</div>
<?php endif; ?>
