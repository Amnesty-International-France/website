<?php
/**
 * Militants resources partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

global $wp;

$taxonomies  = amnesty_get_object_taxonomies('document', 'objects');
$taxonomies = array_filter(
    $taxonomies,
    static fn ($t) => in_array($t->name, ['document_militant_type'], true)
);
$form_url = home_url(add_query_arg([], $wp->request));
?>

<section class="document-list-filters">
	<form id="filter-form" class="news-filters" action="<?php echo esc_url($form_url); ?>">
		<?php require locate_template('partials/forms/taxonomy-filters-militants-resources.php'); ?>
	</form>
	<section class="document-list-search">
		<form method="GET" action="<?php echo esc_url($form_url); ?>">
			<?php require locate_template('partials/forms/militants-resources-searchbar.php'); ?>
		</form>
	</section>
</section>
