<?php

/**
 * Global partial, site header, petitions post type
 *
 * @package Amnesty\Partials
 */

$hero_data = wp_parse_args(
	amnesty_get_header_data( get_the_ID() ),
	[
		'imageID' => absint( get_post_meta( get_the_ID(), '_thumbnail_id', true ) ),
	]
);

$hero_show  = 0 !== $hero_data['imageID'];
$body_class = [];
if ( $hero_show ) {
	$body_class[] = 'has-hero';
}

?>
<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?php echo esc_html( trim( wp_title( '|', false, 'right' ) ?: /* translators: [front] */ __( 'Amnesty International', 'aibrand' ), " \t\n\r\v\0|" ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( $body_class ); ?>>
<?php

do_action( 'wp_body_open' );

if ( $hero_show ) {
	// phpcs:ignore
	echo \Amnesty\Blocks\amnesty_render_header_block( $hero_data );
	amnesty_remove_header_from_content();
}
