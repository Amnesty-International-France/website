<?php
/**
 * Search partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

if ((!isset($taxonomies) || empty($taxonomies) ) && (!isset($types) || empty($types))) {
    return;
}
?>
<div class="taxonomyArchive-filters" aria-label="<?php esc_attr_e('Filter results by topic', 'amnesty'); ?>">
	<?php if( isset($types) ) : ?>
		<?php
		$options = [];
		foreach ($types as $type) {
			$options[$type->name] = $type->labels->name;
		}
		$query_vars = [];
		if( isset($_GET['qtype']) && ! empty($_GET['qtype']) ) {
			$query_vars = explode(',', $_GET['qtype']);
			$query_vars = array_map('trim', $query_vars);
			$query_vars = array_filter($query_vars);
			$query_vars = array_map('sanitize_key', $query_vars);
		}
		amnesty_render_custom_select([
			'label' => 'Type de contenu',
			'name' => 'qtype',
			'active' => $query_vars,
			'options' => $options,
			'multiple' => true,
		]);
		?>
	<?php endif; ?>
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
