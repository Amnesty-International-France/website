<?php
/**
 * Training CPT filters partial
 *
 * @package Amnesty\Partials
 */

global $wpdb;

$active_category = isset($_GET['qcategories']) ? explode(',', sanitize_text_field($_GET['qcategories'])) : [];
$category_options = ['' => 'Toutes les catégories'];
$cat_field_obj = get_field_object('field_688344d2380a3');
if ($cat_field_obj && isset($cat_field_obj['choices'])) {
    $category_options += $cat_field_obj['choices'];
}

$active_location = isset($_GET['qlieu']) ? explode(',', sanitize_text_field($_GET['qlieu'])) : [];
$location_options = ['' => 'Tous les lieux'];
$lieu_field_obj = get_field_object('field_6883319051ddc');
if ($lieu_field_obj && isset($lieu_field_obj['choices'])) {
    $location_options += $lieu_field_obj['choices'];
}

$active_period = isset($_GET['qperiod']) ? explode(',', sanitize_text_field($_GET['qperiod'])) : [];
$periods_query = $wpdb->get_col("SELECT DISTINCT DATE_FORMAT(meta_value, '%Y-%m') FROM {$wpdb->postmeta} WHERE meta_key LIKE '%session%date%de%debut' AND meta_value IS NOT NULL AND meta_value != '' ORDER BY meta_value ASC");
$period_options = ['' => 'Toutes les périodes'];
if (!empty($periods_query)) {
    foreach ($periods_query as $period) {
        if (empty($period)) {
            continue;
        }
        try {
            $date_obj = new DateTime($period . '-01');
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
            $label = ucfirst($formatter->format($date_obj));
            $period_options[$period] = $label;
        } catch (Exception $e) {
            continue;
        }
    }
}
?>

<div class="taxonomyArchive-filters">
    <?php
    amnesty_render_custom_select(['label' => 'Catégorie', 'name' => 'qcategories', 'active' => $active_category, 'options' => $category_options, 'multiple' => true]);
amnesty_render_custom_select(['label' => 'Lieu', 'name' => 'qlieu', 'active' => $active_location, 'options' => $location_options, 'multiple' => true]);
amnesty_render_custom_select(['label' => 'Date', 'name' => 'qperiod', 'active' => $active_period, 'options' => $period_options, 'multiple' => true]);
?>
</div>
<button id="training-filters-submit" class="filter-button" type="submit">Filtrer</button>
