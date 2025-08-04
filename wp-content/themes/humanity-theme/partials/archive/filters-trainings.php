<?php
/**
 * Archives partial, trainings filters (wrapper)
 *
 * @package Amnesty\Partials
 */

global $wp;
$current_post_type = get_query_var('post_type') ?: 'post';
$form_url = get_post_type_archive_link($current_post_type);

?>

<section class="postlist-categoriesContainer" data-slider>
    <form id="filter-form" class="trainings-filters" action="<?php echo esc_url($form_url); ?>" method="get">
        <?php require locate_template('partials/forms/trainings-filters.php');?>
    </form>
</section>
