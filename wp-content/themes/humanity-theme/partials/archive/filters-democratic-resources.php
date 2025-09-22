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
    static fn ($t) => in_array($t->name, ['document_democratic_type', 'document_instance_type'], true)
);

$form_url = home_url(add_query_arg([], $wp->request));
?>

<section class="document-list-filters">
	<form id="filter-form" class="news-filters" action="<?php echo esc_url($form_url); ?>">
		<?php require locate_template('partials/forms/taxonomy-filters-democratic-resources.php'); ?>
	</form>
	<section class="document-list-search">
		<form method="GET" action="<?php echo esc_url($form_url); ?>">
			<?php require locate_template('partials/forms/democratic-resources-searchbar.php'); ?>
		</form>
	</section>
</section>
