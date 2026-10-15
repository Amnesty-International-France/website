<?php

/**
 * Title: Page Change Their History Hero
 * Description: Outputs the Change Their History page hero.
 * Slug: amnesty/page-change-their-history-hero
 * Inserter: no
 */

declare(strict_types=1);

$page_id          = (int) get_the_ID();
$hero_title       = get_field('change_their_history_hero_title', $page_id) ?: get_the_title();
$hero_description = get_field('change_their_history_hero_description', $page_id);
$active_campaign  = amnesty_get_active_clh_campaign_for_page($page_id);

// Le dernier mot du titre passe sur l'aplat jaune : « changez » se lit sur le mot qui change.
$title_words = preg_split('/\s+/u', trim($hero_title)) ?: [ $hero_title ];
$title_last  = count($title_words) > 1 ? array_pop($title_words) : '';
$title_head  = implode(' ', $title_words);

?>

<section class="clh-hero <?php echo $active_campaign ? 'is-running' : 'is-closed'; ?>">
	<div class="clh-hero-inner">
		<div class="clh-hero-stage">
			<div class="clh-hero-statement">
				<h1 class="clh-hero-title">
					<span class="clh-hero-title-line"><?php echo esc_html($title_head); ?></span>
					<?php if ($title_last) : ?>
						<span class="clh-hero-title-line is-marked"><?php echo esc_html($title_last); ?></span>
					<?php endif; ?>
				</h1>

				<?php if ($hero_description) : ?>
					<p class="clh-hero-standfirst"><?php echo esc_html($hero_description); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php
get_template_part('partials/clh-signature-band', null, [ 'variant' => 'sticky' ]);

if (! is_admin()) {
    add_filter('the_content', 'amnesty_remove_first_hero_from_content', 0);
}
