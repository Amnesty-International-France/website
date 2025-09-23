<?php

/**
 * Title: Chronicle Hero
 * Description: Contains the logic to display the correct hero for chronicle pages.
 * Slug: amnesty/hero-chronicle
 * Inserter: no
 */

$chronicle_info = amnesty_get_chronicle_structure_info();
$promo_page_id = !empty($chronicle_info) ? $chronicle_info['promo_page_id'] : null;

$hero_attributes = [
    'titleFirstPart' => 'Magazine La Chronique',
];

if (is_singular('chronique')) {
    $hero_attributes['titleLastPart'] = get_the_title();
} elseif (is_page()) {
    $current_page = get_queried_object();
    if ($current_page && $current_page->post_parent === $promo_page_id && $current_page->post_name === 'archives') {
        $hero_attributes['titleLastPart'] = 'Archives';
    }
}

if ($promo_page_id) {
    $hero_attributes['imagePostId'] = $promo_page_id;
    $hero_attributes['btnLinkText'] = get_field('btn_link_text', $promo_page_id);
    $hero_attributes['btnLink'] = get_field('btn_link', $promo_page_id);
}

$hero_block_comment = sprintf(
    '<!-- wp:amnesty-core/hero-large %s /-->',
    wp_json_encode($hero_attributes)
);

echo do_blocks($hero_block_comment);
