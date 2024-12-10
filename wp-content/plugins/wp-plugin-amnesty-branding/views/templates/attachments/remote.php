<?php

/**
 * Attachment template, remote site
 *
 * @package Amnesty\Templates
 */

get_header();

$should_switch = ! empty( $post->blog_id ) && absint( $post->blog_id ) !== absint( get_current_blog_id() );

if ( $should_switch ) {
	switch_to_blog( $post->blog_id );
}

$recipients    = get_letter_recipients();
$reduced_width = get_post_meta( get_the_ID(), '_reduce_content_width', true );
$term_list     = wp_get_object_terms( get_the_ID(), get_taxonomies( [ 'public' => true ] ) );
$primary_terms = function_exists( 'get_document_keywords' ) ? get_document_keywords( $term_list ) : [];
$doc_language  = get_post_meta( get_the_ID(), 'amnesty_language_code', true );
$textdirection = get_string_textdirection( get_the_title() );

$category = get_the_category();
if ( count( $category ) > 0 ) {
	$category = array_shift( $category );
}

if ( $should_switch ) {
	restore_current_blog();
}

?>

<main id="main">
	<div class="section section--small container article-container">
		<section class="article has-sidebar">
			<header class="article-header <?php $reduced_width && print 'is-narrow'; ?>" style="<?php echo esc_attr( 'rtl' === $textdirection ? 'text-align:right' : '' ); ?>">
				<?php get_template_part( 'partials/single/meta-area', null, compact( 'should_switch' ) ); ?>
				<h1 id="article-title" class="article-title" lang="<?php echo esc_attr( $doc_language ); ?>" dir="<?php echo esc_attr( $textdirection ); ?>"><?php the_title(); ?></h1>
			</header>

			<article class="article-content <?php $reduced_width && print 'is-narrow'; ?>" aria-labelledby="article-title" lang="<?php echo esc_attr( $doc_language ); ?>" dir="<?php echo esc_attr( $textdirection ); ?>">
				<?php require plugin_dir_path( AIBRAND_FILE ) . 'views/partials/single/recipients.php'; ?>
				<?php the_content(); ?>
			</article>

			<footer class="article-footer">
				<?php get_template_part( 'partials/single/term-area', null, compact( 'should_switch' ) ); ?>
			</footer>
		</section>

		<aside class="article-sidebar" aria-label="<?php /* translators: [front] ARIA */ esc_attr_e( 'Sidebar', 'aibrand' ); ?>">
			<?php get_sidebar(); ?>
		</aside>
	</div>
</main>

<?php get_footer(); ?>
