<?php

declare(strict_types=1);

/**
 * EDH CPT filters partial
 *
 * @package Amnesty\Partials
 */

global $wpdb;

$active_content_type = isset($_GET['qcontent_type']) ? explode(',', sanitize_text_field($_GET['qcontent_type'])) : [];
$content_type_options = ['' => 'Tous les types de contenus'];
$cat_field_obj = get_field_object('field_6892103b14459');
if ($cat_field_obj && isset($cat_field_obj['choices'])) {
    $content_type_options += $cat_field_obj['choices'];
}

$active_theme = isset($_GET['qtheme']) ? explode(',', sanitize_text_field($_GET['qtheme'])) : [];
$theme_options = ['' => 'Tous les theme'];
$theme_field_obj = get_field_object('field_689210a11445a');
if ($theme_field_obj && isset($theme_field_obj['choices'])) {
    $theme_options += $theme_field_obj['choices'];
}

$active_requirements = isset($_GET['qrequirements']) ? explode(',', sanitize_text_field($_GET['qrequirements'])) : [];
$requirements_options = ['' => 'Tous les Besoins'];
$requirements_field_obj = get_field_object('field_689210be1445b');
if ($requirements_field_obj && isset($requirements_field_obj['choices'])) {
    $requirements_options += $requirements_field_obj['choices'];
}

$active_activity_duration = isset($_GET['qactivity_duration']) ? explode(',', sanitize_text_field($_GET['qactivity_duration'])) : [];
$activity_duration_options = ['' => 'Tous les Besoins'];
$activity_duration_field_obj = get_field_object('field_689210f30c5c4');
if ($activity_duration_field_obj && isset($activity_duration_field_obj['choices'])) {
    $activity_duration_options += $activity_duration_field_obj['choices'];
}

?>

<div class="taxonomyArchive-filters">
    <?php
    amnesty_render_custom_select(['label' => 'Type de contenu', 'name' => 'qcontent_type', 'active' => $active_content_type, 'options' => $content_type_options, 'multiple' => true]);
amnesty_render_custom_select(['label' => 'Thème', 'name' => 'qtheme', 'active' => $active_theme, 'options' => $theme_options, 'multiple' => true]);
amnesty_render_custom_select(['label' => 'Besoins', 'name' => 'qrequirements', 'active' => $active_requirements, 'options' => $requirements_options, 'multiple' => true]);
amnesty_render_custom_select(['label' => 'Durée de l\'activité', 'name' => 'qactivity_duration', 'active' => $active_activity_duration, 'options' => $activity_duration_options, 'multiple' => true]);
?>
</div>
<button id="training-filters-submit" class="filter-button" type="submit">Filtrer</button>
