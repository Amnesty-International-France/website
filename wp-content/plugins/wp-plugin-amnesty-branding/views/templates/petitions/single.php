<?php

/**
 * Single template, petitions post type
 *
 * @package Amnesty\Templates
 */

require dirname( AIBRAND_FILE ) . '/views/partials/petitions/header.php';
the_post();

?>

<main id="main">
<?php if ( get_transient( 'amnesty_petitions_errors' ) ) : ?>
	<div class="section section--small container u-textCenter">
		<?php do_action( 'amnesty_petitions_errors' ); ?>
	</div>
<?php endif; ?>

	<div class="section section--small container article-container">
		<?php the_content(); ?>
	</div>
</main>

<?php get_footer(); ?>
