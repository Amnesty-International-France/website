<?php
/**
 * Title: Archive Chapo
 * Description: Outputs chapo block for post type landmark and country archive pages
 * Slug: amnesty/archive-chapo
 * Inserter: no
 */

$chapo_text = '';

if (is_post_type_archive('fiche_pays')) {
    $chapo_text = get_option('countries_global_chapo', '');
}

if (is_post_type_archive('landmark')) {
    $chapo_text = get_option('landmark_global_chapo', '');
}

?>

<?php if (!empty($chapo_text)) : ?>
    <div class="chapo">
        <p class="text"><?php echo wp_kses_post(nl2br( stripslashes($chapo_text))); ?></p>
    </div>
<?php endif; ?>
