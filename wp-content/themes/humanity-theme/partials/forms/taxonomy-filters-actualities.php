<?php

/**
 * Search partial, taxonomy filters fot actualities in my space
 *
 * @package Amnesty\Partials
 */

if ((!isset($taxonomies) || empty($taxonomies)) && (!isset($types) || empty($types))) {
    return;
}

?>
<div class="taxonomyArchive-filters" aria-label="<?php esc_attr_e('Filter results by topic', 'amnesty'); ?>">
    <?php foreach ($taxonomies as $tax_item) : ?>
        <?php
        amnesty_render_custom_select([
            'label'    => $tax_item->label,
            'show_label' => true,
            'name'     => "q{$tax_item->name}",
            'active'   => amnesty_get_query_var("q{$tax_item->name}"),
            'options'  => amnesty_taxonomy_to_option_list($tax_item),
        ]);
        ?>
    <?php endforeach; ?>
</div>
<a type="button" id="search-filters-submit" href="<?php echo esc_url($form_url)?>" class="filter-button">Réinitialiser</a>
