<?php

/**
 * Title: Post Back Link
 * Description: Output back link to return to item's category archive
 * Slug: amnesty/post-back-link
 * Inserter: no
 */

$show_back_link = !amnesty_validate_boolish( amnesty_get_option( '_display_category_label' ));

if (!$show_back_link) {
    return;
}

$main_category = amnesty_get_a_post_term( get_the_ID() );

if ($main_category) {
    $chip_style = match ($main_category->slug) {
        'actualites', 'dossiers', 'campagnes' => 'bg-black',
        'chroniques' => 'bg-yellow',
        default => 'black-outline',
    };
    $label = $main_category->name;
    $link = amnesty_term_link($main_category);
} else {
    $chip_style = 'bg-black'; 
    $post_type_object = get_post_type_object('fiche_pays');
    $label = $post_type_object->labels->singular_name;
    $link = get_post_type_archive_link(get_post_type());
}

?>

<!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($label); ?>","link":"<?php echo esc_url($link); ?>","size":"large","style":"<?php echo esc_attr($chip_style); ?>"} /-->
