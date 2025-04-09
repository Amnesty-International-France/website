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

if (!$main_category) {
	return;
}

$chip_style = match ($main_category->slug) {
    'actualites', 'dossier' => 'bg-black',
    'chroniques' => 'bg-yellow',
    default => 'black-outline',
};

?>

<!-- wp:amnesty-core/chip-category {"label":"<?php echo esc_html($main_category->name); ?>","link":"<?php echo esc_url(amnesty_term_link( $main_category )); ?>","size":"large","style":"<?php echo esc_attr($chip_style); ?>"} /-->
