<?php
/**
 * Archives partial, trainings filters (wrapper)
 *
 * @package Amnesty\Partials
 */

global $wp;
$form_url = home_url(add_query_arg([], $wp->request));
?>

<section class="postlist-categoriesContainer" data-slider>
    <form id="filter-form" class="trainings-filters" action="<?php echo esc_url($form_url); ?>" method="get">
        <?php require locate_template('partials/forms/trainings-filters.php');?>
    </form>
</section>
