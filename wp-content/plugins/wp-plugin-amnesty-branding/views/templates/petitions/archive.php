<?php

/**
 * Petitions post type archive template
 *
 * @package Amnesty\Templates
 */

get_header();

// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE
$user_signed_petitions = sanitize_text_field( $_COOKIE['amnesty_petitions'] ?? '' );
if ( $user_signed_petitions ) {
	$user_signed_petitions = array_map( 'absint', explode( ',', $user_signed_petitions ) );
}

?>
<main id="main">
	<div class="container">
		<section class="news-section section section--small section--topSpacing" aria-label="<?php /* translators: [front] ARIA https://wordpresstheme.amnesty.org/the-theme/templates/b050-petition-index/ */ esc_attr_e( 'All Petitions', 'aip' ); ?>">
			<?php

			echo wp_kses_post(
				amnesty_render_petition_list_block(
					[
						'type'  => 'template',
						'style' => 'petition',
						'query' => $GLOBALS['wp_query'],
					]
				)
			);

			?>
		</section>
	</div>
</main>
<?php get_footer(); ?>
