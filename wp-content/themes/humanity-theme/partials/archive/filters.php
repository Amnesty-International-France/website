<?php

/**
 * Archives partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

$taxonomies = amnesty_get_object_taxonomies( 'post', 'objects' );
$form_url = get_post_type_archive_link( 'post' );

if ( is_category() ) {
	unset( $taxonomies['category'] );

	$form_url = get_category_link( get_queried_object_id() );
}

if ( ! $taxonomies ) {
	return;
}

?>
<section class="postlist-categoriesContainer" data-slider>
	<form id="filter-form" class="news-filters" action="<?php echo esc_url( $form_url ); ?>">
		<?php require locate_template( 'partials/forms/taxonomy-filters.php' ); ?>
	</form>
</section>
