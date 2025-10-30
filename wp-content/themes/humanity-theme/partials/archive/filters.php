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
    $taxonomies = [];

    if (is_tax('location')) {
        $term = get_queried_object();
        if ($term) {
            $form_url = home_url('/categorie/' . $term->slug . '/');
        }
    }
    $types = get_post_types(['public' => true, ], 'objects');
    $filters = ['post', 'press-release', 'petition', 'document', 'landmark'];
    $types = array_filter($types, static fn ($t) => in_array($t->name, $filters, true));
} elseif (is_post_type_archive()) {
    $form_url = get_post_type_archive_link($post_type);
} else {
    $form_url = home_url(add_query_arg([], $wp->request));
}

if (!$taxonomies && ! isset($types)) {
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

