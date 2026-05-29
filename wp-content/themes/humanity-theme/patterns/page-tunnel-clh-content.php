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
    exit();
}

$start_date = get_field('start_date_highligth_clh', $parent);
$end_date = get_field('end_date_highlight_clh', $parent);
$timestamp_now = time();
$timestamp_start_date = $start_date ? strtotime($start_date) : 0;
$timestamp_end_date = $end_date ? strtotime($end_date) : 0;
$countdown = $timestamp_end_date - $timestamp_now;

if ($timestamp_start_date > $timestamp_now) {
    wp_redirect('/');
    exit();
}

if ($countdown <= 0) {
    wp_redirect('/');
    exit();
}

$raw_email = $_GET['email'] ?? null;
$last_signer_email = ($raw_email && is_email($raw_email)) ? sanitize_email($raw_email) : null;
$current_user = get_local_user($last_signer_email);
$list_petitions_clh = get_field('list_petition_clh', $parent);

if (empty($list_petitions_clh)) {
    wp_redirect('/');
    exit();
}

$selected_posts = [];

foreach ($list_petitions_clh as $petition) {
    $selected_posts[] = [
        'id' => $petition->ID,
        'title' => $petition->post_title,
        'already_signed' => $last_signer_email && $current_user && have_signed($petition->ID, $current_user->id),
    ];
}

$not_signed = \array_filter($selected_posts, static fn ($petition) => $petition['already_signed'] === false);

if (empty($not_signed)) {
    wp_redirect('/');
    exit();
}

$random_key = array_rand($not_signed);
$next_petition = $not_signed[$random_key];
$next_petition['link'] = get_permalink($next_petition['id']);
$next_petition['featured_media_url'] = get_the_post_thumbnail_url($next_petition['id'], 'full');

?>
<article class="wp-block-group page">
	<div>
		<?php  echo esc_html($next_petition['title']); ?>
	</div>
</article>
