<?php
/**
 * Democratic resources partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

global $wp;

$taxonomies  = amnesty_get_object_taxonomies('document', 'objects');
$taxonomies = array_filter(
    $taxonomies,
    static fn ($t) => in_array($t->name, ['location', 'combat'], true)
);

$current_post_type = get_query_var('post_type') ?: 'post';
$form_url = get_post_type_archive_link($current_post_type);

?>

<section class="postlist-categoriesContainer" data-slider>
	<form id="filter-form" class="trainings-filters" action="<?php echo esc_url($form_url); ?>" method="get">
		<?php require locate_template('partials/forms/taxonomy-filters.php');?>
	</form>
</section>
