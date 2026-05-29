<?php

/**
 * Title: Page Change Their History Content Pattern
 * Description: Page content pattern for the theme
 * Slug: amnesty/page-change-their-history-content
 * Inserter: no
 */

$hero_extra_class = ! has_post_thumbnail() ? 'no-featured-image' : '';
$no_chapo = ! has_block('amnesty-core/chapo') ? 'no-chapo' : '';
$page = get_post();
$parent = get_post($page->post_parent);
$has_related_content = !empty(get_field('_related_posts_selected', $page));

$is_highlighted = get_field('highlight_clh', $page->ID);

if ($is_highlighted) {
    $active_campaign = get_field('campaign_clh', $page->ID);

    $start_date = get_field('start_date_highligth_clh', $active_campaign->ID);
    $end_date = get_field('end_date_highlight_clh', $active_campaign->ID) ?? null;
    $timestamp_now = time();
    $timestamp_start_date = strtotime($start_date);
    $timestamp_end_date = strtotime($end_date);
    $countdown = $timestamp_end_date - $timestamp_now;

    if ($timestamp_start_date > $timestamp_now) {
        $is_highlighted = false;
    }

    if ($countdown <= 0) {
        $is_highlighted = false;
    } else {
        $total_number_of_signatures_collected = 0;
        $total_campaign_signatures = 0;

        $list_petition_current_campaign = get_field('list_petition_clh', $active_campaign->ID) ?? [];

        foreach ($list_petition_current_campaign as $single_campaign) {
            $total_campaign_signatures += (int)get_post_meta($single_campaign->ID, 'objectif_signatures', true);
            $total_number_of_signatures_collected += amnesty_get_petition_signature_count($single_campaign->ID) ?: 0;
        }
    }
}

?>

<!-- wp:group {"tagName":"page","className":"page"} -->
<article class="wp-block-group page <?php print esc_attr($class_name ?? ''); ?>">
	<!-- wp:group {"tagName":"section","className":"page-content"} -->
	<?php if ($is_highlighted) : ?>
		<div>
			Signature accumulé: <?php echo $total_number_of_signatures_collected ?? 0; ?>
		</div>
		<div>
			Objectif signature global: <?php echo $total_campaign_signatures ?? 0; ?>
		</div>
		<div id="countdown" data-countdown="<?php echo esc_attr($countdown); ?>">
			Deadline: <span><?php echo esc_html((string)$countdown); ?></span>
		</div>
	<?php endif; ?>
	<section
		class="wp-block-group page-content <?php echo esc_attr($hero_extra_class); ?> <?php print esc_attr($no_chapo ?? ''); ?>">
		<!-- wp:post-content /-->
	</section>
	<!-- /wp:group -->
	<?php if ($has_related_content): ?>
		<!-- wp:amnesty-core/related-posts {"title":"Voir aussi"} /-->
	<?php endif; ?>
	<?php if (!is_front_page()): ?>
		<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
		<footer class="wp-block-group article-footer">
			<!-- wp:pattern {"slug":"amnesty/post-terms"} /-->
		</footer>
		<!-- /wp:group -->
	<?php endif; ?>
</article>
<!-- /wp:group -->
