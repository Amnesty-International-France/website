<?php

declare(strict_types=1);

/**
 * EDH CPT filters partial
 *
 * @package Amnesty\Partials
 */

global $wpdb;

$active_content_type  = isset($_GET['qtype_de_contenu']) ? sanitize_text_field($_GET['qtype_de_contenu']) : null;
$content_type_options = [ '' => 'Types de contenus' ];
$cat_field_obj        = get_field_object('field_68c2a14b84414');
if ($cat_field_obj && isset($cat_field_obj['choices'])) {
    $content_type_options += $cat_field_obj['choices'];
}

$active_theme    = isset($_GET['qtheme']) ? sanitize_text_field($_GET['qtheme']) : null;
$theme_options   = [ '' => 'Thèmes' ];
$theme_field_obj = get_field_object('field_68c2a1a484415');
if ($theme_field_obj && isset($theme_field_obj['choices'])) {
    $theme_options += $theme_field_obj['choices'];
}

$active_requirements    = isset($_GET['qrequirements']) ? sanitize_text_field($_GET['qrequirements']) : null;
$requirements_options   = [ '' => 'Besoins' ];
$requirements_field_obj = get_field_object('field_68c2a1f984416');
if ($requirements_field_obj && isset($requirements_field_obj['choices'])) {
    $requirements_options += $requirements_field_obj['choices'];
}

$active_activity_duration    = isset($_GET['qactivity_duration']) ? sanitize_text_field($_GET['qactivity_duration']) : null;
$activity_duration_options   = [ '' => 'Durée' ];
$activity_duration_field_obj = get_field_object('field_68c2a22184417');
if ($activity_duration_field_obj && isset($activity_duration_field_obj['choices'])) {
    $activity_duration_options += $activity_duration_field_obj['choices'];
}

?>

<div class="taxonomyArchive-filters">
	<?php
    amnesty_render_custom_select(
        [
            'label'    => 'Type de contenus',
            'show_label' => true,
            'name'     => 'qtype_de_contenu',
            'active'   => $active_content_type,
            'options'  => $content_type_options,
        ]
    );
amnesty_render_custom_select(
    [
        'label'    => 'Thèmes',
        'show_label' => true,
        'name'     => 'qtheme',
        'active'   => $active_theme,
        'options'  => $theme_options,
    ]
);
amnesty_render_custom_select(
    [
        'label'    => 'Besoins',
        'show_label' => true,
        'name'     => 'qrequirements',
        'active'   => $active_requirements,
        'options'  => $requirements_options,
    ]
);
amnesty_render_custom_select(
    [
        'label'    => 'Durée',
        'show_label' => true,
        'name'     => 'qactivity_duration',
        'active'   => $active_activity_duration,
        'options'  => $activity_duration_options,
    ]
);
?>
</div>
<a type="button" id="training-filters-submit" class="filter-button" href="<?php echo esc_url($form_url)?>">Réinitialiser</a>
