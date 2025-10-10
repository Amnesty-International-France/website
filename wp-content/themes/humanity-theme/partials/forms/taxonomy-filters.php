<?php

/**
 * Search partial, taxonomy filters
 *
 * @package Amnesty\Partials
 */

if ((!isset($taxonomies) || empty($taxonomies)) && (!isset($types) || empty($types))) {
    return;
}
?>
<div class="taxonomyArchive-filters" aria-label="<?php esc_attr_e('Filter results by topic', 'amnesty'); ?>">
    <?php if (isset($types)) : ?>
    <?php
        $options = [];
        foreach ($types as $type) {
            if (in_array($type->name, ['actualities-my-space', 'tribe_events', 'document_democratic_type', 'document_instance_type', 'document_militant_type'])) {
                continue;
            }

            $options[$type->name] = $type->labels->name;
        }
        $query_vars = [];
        if (isset($_GET['qtype']) && ! empty($_GET['qtype'])) {
            $query_vars = explode(',', $_GET['qtype']);
            $query_vars = array_map('trim', $query_vars);
            $query_vars = array_filter($query_vars);
            $query_vars = array_map('sanitize_key', $query_vars);
            $query_vars = array_unique($query_vars);
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

    <?php
    $active_types = [];
if (isset($query_vars) && is_array($query_vars)) {
    $active_types = $query_vars;
} elseif (isset($_GET['qtype']) && ! empty($_GET['qtype'])) {
    $active_types = explode(',', $_GET['qtype']);
    $active_types = array_map('trim', $active_types);
    $active_types = array_filter($active_types);
    $active_types = array_map('sanitize_key', $active_types);
    $active_types = array_unique($active_types);
}

$post_type_qv = get_query_var('post_type');
$petition_qv  = is_array($post_type_qv) ? in_array('petition', $post_type_qv, true) : ($post_type_qv === 'petition');
$is_petition_archive = function_exists('is_post_type_archive') ? is_post_type_archive('petition') : false;
$is_petition_selected = in_array('petition', $active_types, true);
$is_petition_ctx = ($is_petition_selected || $petition_qv || $is_petition_archive);
?>

    <?php foreach ($taxonomies as $tax_item) : ?>
        <?php
    if ($is_petition_ctx && isset($tax_item->name) && $tax_item->name === 'keyword') {
        continue;
    }

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
