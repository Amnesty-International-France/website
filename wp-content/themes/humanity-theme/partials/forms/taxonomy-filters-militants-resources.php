<?php

/**
 * Search partial, taxonomy filters for militants resources in my space
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
            'name'     => "q{$tax_item->name}",
            'active'   => query_var_to_array("q{$tax_item->name}"),
            'options'  => amnesty_taxonomy_to_option_list($tax_item),
            'multiple' => true,
        ]);
        ?>
    <?php endforeach; ?>
</div>
<button id="search-filters-submit" class="filter-button">Filtrer</button>
