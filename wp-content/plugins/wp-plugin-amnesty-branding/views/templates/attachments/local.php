<?php

/**
 * Attachment template
 *
 * @package Amnesty\Templates
 */

get_header();
the_post();


$reduced_width = get_post_meta( get_the_ID(), '_reduce_content_width', true );

?>

<main id="main">
	<div class="section section--small container article-container">
		<section class="article has-sidebar">
			<header class="article-header <?php $reduced_width && print 'is-narrow'; ?>">
				<?php get_template_part( 'partials/single/meta-area' ); ?>
				<h1 id="article-title" class="article-title"><?php the_title(); ?></h1>
			</header>

			<article class="article-content <?php $reduced_width && print 'is-narrow'; ?>" aria-labelledby="article-title">
				<?php require plugin_dir_path( AIBRAND_FILE ) . 'views/partials/single/recipients.php'; ?>
				<?php the_content(); ?>
			</article>

			<footer class="article-footer">
				<?php get_template_part( 'partials/single/term-area' ); ?>
			</footer>
		</section>

		<aside class="article-sidebar" aria-label="<?php /* translators: [front] ARIA */ esc_attr_e( 'Sidebar', 'aibrand' ); ?>">
			<?php get_sidebar(); ?>
		</aside>
	</div>
</main>

<?php get_footer(); ?>
