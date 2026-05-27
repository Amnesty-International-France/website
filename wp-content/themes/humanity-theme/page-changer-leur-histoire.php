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
    } else {
        $total_number_of_signatures_collected = 0;
        $total_campaign_signatures = 0;

        $list_petition_current_campaign = get_field('list_petition_clh', $post->ID) ?? [];

        foreach ($list_petition_current_campaign as $single_campaign) {
            $total_campaign_signatures += (int) get_post_meta($single_campaign->ID, 'objectif_signatures', true);
            $total_number_of_signatures_collected += amnesty_get_petition_signature_count($single_campaign->ID) ?: 0;
        }
    }
}

get_header();
?>

<main class="wp-block-group page-standard">
    <?php echo render_block(['blockName' => 'core/pattern', 'attrs' => ['slug' => 'amnesty/page-hero']]); ?>
    <div class="wp-block-group container page-container has-gutter">
		<?php if ($is_highlight_time) : ?>
			<div>
				Signature accumulé: <?php echo $total_number_of_signatures_collected; ?>
			</div>
			<div>
				Objectif signature global: <?php echo $total_campaign_signatures; ?>
			</div>
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
