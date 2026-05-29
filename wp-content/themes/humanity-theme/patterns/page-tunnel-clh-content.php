<?php

declare(strict_types=1);

/**
 * Title: Page Change Their History Tunnel Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-tunnel-clh-content
 * Inserter: no
 */


$post = get_post();
$parent = $post->post_parent;

$is_highlighted = get_field('highlight_clh', $parent);

if (!$is_highlighted) {
    wp_redirect('/');
}

$start_date = get_field('start_date_highligth_clh', $parent);
$end_date = get_field('end_date_highlight_clh', $parent) ?? null;
$timestamp_now = time();
$timestamp_start_date = strtotime($start_date);
$timestamp_end_date = strtotime($end_date);
$countdown = $timestamp_end_date - $timestamp_now;

if ($timestamp_start_date > $timestamp_now) {
    $is_highlighted = false;
    wp_redirect('/');
}

if ($countdown <= 0) {
    $is_highlighted = false;
    wp_redirect('/');
}


$list_petitions_clh = get_field('list_petition_clh', $parent);
$selected_posts = [];

foreach ($list_petitions_clh as $petition) {
    $selected_posts[] = [
        'id' => $petition->ID,
        'title' => $petition->post_title,
        'link' => get_permalink($petition->ID),
        'featured_media_url' => get_the_post_thumbnail_url($petition->ID, 'full'),
        'already_signed' => have_signed($petition->ID, 1),
    ];
}


$not_signed = \array_filter($selected_posts, static fn ($petition) => $petition['already_signed'] === false);
$random_key = array_rand($not_signed);

$next_petition = $not_signed[$random_key];

?>
<article
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<div>
		<?php  echo $next_petition['title']; ?>
	</div>
</article>
