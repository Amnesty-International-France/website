<?php
/**
 * Archives partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

global $wp;

$post_type = get_query_var('post_type') ?: 'post';

$taxonomies = amnesty_get_object_taxonomies($post_type, 'objects');

if (is_category()) {
    unset($taxonomies['category']);
    $form_url = get_category_link(get_queried_object_id());
} elseif (is_tax()) {
    $form_url = get_term_link(get_queried_object());
} elseif (is_post_type_archive()) {
    $form_url = get_post_type_archive_link($post_type);
} else {
    $form_url = home_url(add_query_arg([], $wp->request));
}

if (!$taxonomies) {
    return;
}
?>

<?php if (get_post_type() !== 'fiche_pays') : ?>
    <section class="postlist-categoriesContainer" data-slider>
        <form id="filter-form" class="news-filters" action="<?php echo esc_url($form_url); ?>">
            <?php require locate_template('partials/forms/taxonomy-filters.php'); ?>
        </form>
    </section>
<?php endif; ?>

