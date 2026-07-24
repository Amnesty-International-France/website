<?php

/**
 * Riposte archive filters.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$available = get_object_taxonomies('riposte_victory', 'objects');

$taxonomies = [];

foreach (['location', 'riposte_theme'] as $taxonomy) {
	if (isset($available[$taxonomy])) {
		$taxonomies[$taxonomy] = $available[$taxonomy];
	}
}

if (empty($taxonomies)) {
	return;
}

$form_url = get_post_type_archive_link('riposte_victory');

if (! $form_url) {
	return;
}
?>

<section class="postlist-categoriesContainer" data-slider>
	<form id="filter-form" class="news-filters" action="<?php echo esc_url($form_url); ?>">
		<?php require locate_template('partials/forms/taxonomy-filters.php'); ?>
	</form>
</section>