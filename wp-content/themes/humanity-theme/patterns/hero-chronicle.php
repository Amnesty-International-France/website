<?php

/**
 * Title: Chronicle Hero
 * Description: Contains the logic to display the correct hero for chronicle pages.
 * Slug: amnesty/hero-chronicle
 * Inserter: no
 */

$hero_attributes = [
    'titleFirstPart' => 'Magazine La Chronique',
];

if (is_singular('chronique')) {
    $hero_attributes['titleLastPart'] = get_the_title();
} elseif (is_page_template('templates/archive-chronique.html')) {
    $hero_attributes['titleLastPart'] = 'Archives';
}

$promo_page = get_page_by_path('chronique');
if ($promo_page) {
    $hero_attributes['imagePostId'] = $promo_page->ID;
}

if ($promo_page) {
    $hero_attributes['btnLinkText'] = get_field('btn_link_text', $promo_page->ID);
    $hero_attributes['btnLink'] = get_field('btn_link', $promo_page->ID);
}

$hero_block_comment = sprintf(
    '<!-- wp:amnesty-core/hero-large %s /-->',
    wp_json_encode($hero_attributes)
);

echo do_blocks($hero_block_comment);
