<?php

declare(strict_types=1);

$post = get_post();

$is_highlight_time = get_field('highlight_clh', $post->ID);

if ($is_highlight_time) {
    $end_date = get_field('end_date_highlith_clh', $post->ID) ?? null;
    $timestamp_end = strtotime((string) $end_date);

    $timestamp_now = time();
    $countdown = $timestamp_end - $timestamp_now;

    if ($countdown <= 0) {
        $is_highlight_time = false;
    }
}

get_header();
?>

<main class="wp-block-group page-standard">
    <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/page-hero']]); ?>
    <div class="wp-block-group container page-container has-gutter">
		<?php if ($is_highlight_time) : ?>
			<div id="countdown" data-countdown="<?php echo esc_attr($countdown); ?>">
				Deadline: <span><?php echo esc_html((string)$countdown); ?></span>
			</div>
		<?php endif; ?>
		<?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/page-change-their-history-content']]); ?>
	</div>
</main>

<?php
block_template_part('footer');
get_footer();
