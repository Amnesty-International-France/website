<?php

declare( strict_types = 1 );

add_filter( 'big_bite_block_tabbed_content_show_tab_id_settings', '__return_true' );

if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

require_once __DIR__ . '/_deprecated/header/class-header-block-renderer.php';
require_once __DIR__ . '/_deprecated/header/register.php';
require_once __DIR__ . '/action/register.php';
require_once __DIR__ . '/actions-homepage/register.php';
require_once __DIR__ . '/actions-homepage/render.php';
require_once __DIR__ . '/agenda-homepage/register.php';
require_once __DIR__ . '/agenda-homepage/render.php';
require_once __DIR__ . '/agir-legacy/register.php';
require_once __DIR__ . '/agir-legacy/render.php';
require_once __DIR__ . '/article-card/register.php';
require_once __DIR__ . '/article-card/render.php';
require_once __DIR__ . '/articles-homepage/register.php';
require_once __DIR__ . '/articles-homepage/render.php';
require_once __DIR__ . '/banner/register.php';
require_once __DIR__ . '/button/register.php';
require_once __DIR__ . '/button/render.php';
require_once __DIR__ . '/call-to-action/register.php';
require_once __DIR__ . '/call-to-action/render.php';
require_once __DIR__ . '/card-image-text/register.php';
require_once __DIR__ . '/card-image-text/render.php';
require_once __DIR__ . '/carousel/register.php';
require_once __DIR__ . '/carousel/render.php';
require_once __DIR__ . '/chapo/register.php';
require_once __DIR__ . '/chip-category/register.php';
require_once __DIR__ . '/chip-category/render.php';
require_once __DIR__ . '/collapsable/register.php';
require_once __DIR__ . '/collapsable/render.php';
require_once __DIR__ . '/countdown-timer/register.php';
require_once __DIR__ . '/countdown-timer/render.php';
require_once __DIR__ . '/custom-card/register.php';
require_once __DIR__ . '/custom-card/render.php';
require_once __DIR__ . '/donation-calculator/register.php';
require_once __DIR__ . '/donation-calculator/render.php';
require_once __DIR__ . '/download/register.php';
require_once __DIR__ . '/download/render.php';
require_once __DIR__ . '/download-go-further/register.php';
require_once __DIR__ . '/download-go-further/render.php';
require_once __DIR__ . '/embed-flourish/register.php';
require_once __DIR__ . '/embed-flourish/render.php';
require_once __DIR__ . '/embed-infogram/register.php';
require_once __DIR__ . '/embed-infogram/render.php';
require_once __DIR__ . '/embed-sutori/register.php';
require_once __DIR__ . '/embed-sutori/render.php';
require_once __DIR__ . '/embed-tickcounter/register.php';
require_once __DIR__ . '/embed-tickcounter/render.php';
require_once __DIR__ . '/event-card/register.php';
require_once __DIR__ . '/event-card/render.php';
require_once __DIR__ . '/get-informed/register.php';
require_once __DIR__ . '/get-informed/render.php';
require_once __DIR__ . '/hero/helpers.php';
require_once __DIR__ . '/hero/register.php';
require_once __DIR__ . '/hero/render.php';
require_once __DIR__ . '/hero-homepage/register.php';
require_once __DIR__ . '/hero-homepage/render.php';
require_once __DIR__ . '/iframe-button/register.php';
require_once __DIR__ . '/iframe-button/render.php';
require_once __DIR__ . '/iframe/register.php';
require_once __DIR__ . '/iframe/render.php';
require_once __DIR__ . '/image/register.php';
require_once __DIR__ . '/image/render.php';
require_once __DIR__ . '/key-figure/register.php';
require_once __DIR__ . '/link-icon/register.php';
require_once __DIR__ . '/link-group/register.php';
require_once __DIR__ . '/link-group/render.php';
require_once __DIR__ . '/links-with-icons/register.php';
require_once __DIR__ . '/links-with-icons/render.php';
require_once __DIR__ . '/menu/register.php';
require_once __DIR__ . '/menu/render.php';
require_once __DIR__ . '/mission-homepage/register.php';
require_once __DIR__ . '/mission-homepage/render.php';
require_once __DIR__ . '/petition-list/register.php';
require_once __DIR__ . '/petition-list/render.php';
require_once __DIR__ . '/post-list/register.php';
require_once __DIR__ . '/post-list/render.php';
require_once __DIR__ . '/post-meta/register.php';
require_once __DIR__ . '/post-meta/render.php';
require_once __DIR__ . '/quote/register.php';
require_once __DIR__ . '/quote/render.php';
require_once __DIR__ . '/raw-code/register.php';
require_once __DIR__ . '/read-also/register.php';
require_once __DIR__ . '/read-also/render.php';
require_once __DIR__ . '/read-more/register.php';
require_once __DIR__ . '/read-more/render.php';
require_once __DIR__ . '/regions/register.php';
require_once __DIR__ . '/regions/render.php';
require_once __DIR__ . '/related-content/register.php';
require_once __DIR__ . '/related-content/render.php';
require_once __DIR__ . '/related-posts/register.php';
require_once __DIR__ . '/related-posts/render.php';
require_once __DIR__ . '/section/register.php';
require_once __DIR__ . '/section/render.php';
require_once __DIR__ . '/section-home/register.php';
require_once __DIR__ . '/slider/register.php';
require_once __DIR__ . '/slider/render.php';
require_once __DIR__ . '/stat-counter/register.php';
require_once __DIR__ . '/stat-counter/render.php';
require_once __DIR__ . '/term-list/register.php';
require_once __DIR__ . '/term-list/render.php';
require_once __DIR__ . '/tweet-action/register.php';
require_once __DIR__ . '/tweet-action/render.php';
require_once __DIR__ . '/video/register.php';
require_once __DIR__ . '/video/render.php';

if ( ! function_exists( 'amnesty_register_php_rendered_blocks' ) ) {
	/**
	 * Register the blocks that require php to be rendered.
	 *
	 * @package Amnesty\Blocks
	 *
	 * @return void
	 */
	function amnesty_register_php_rendered_blocks() {
		register_action_block();
		register_actions_homepage_block();
		register_agenda_homepage_block();
		register_agir_legacy_block();
		register_article_card_block();
		register_articles_homepage_block();
		register_banner_block();
		register_button_block();
		register_carousel_block();
		register_card_image_text_block();
		register_chapo_block();
		register_chip_category_block();
		register_collapsable_block();
		register_countdown_block();
		register_call_to_action_block();
		register_custom_card_block();
		register_donation_calculator_block();
		register_download_block();
		register_download_go_further_block();
		register_event_card_block();
		register_flourish_embed_block();
		register_get_informed_block();
		register_header_block();
		register_hero_block();
		register_hero_homepage_block();
		register_iframe_block();
		register_iframe_button_block();
		register_image_block();
		register_infogram_embed_block();
		register_key_figure_block();
		register_link_icon_block();
		register_link_group_block();
		register_links_with_icons_block();
		register_list_block();
		register_menu_block();
		register_mission_homepage_block();
		register_petition_list_block();
		register_quote_block();
		register_raw_code_block();
		register_read_also_block();
		register_read_more_block();
		register_regions_block();
		register_related_content_block();
		register_related_posts_block();
		register_section_block();
		register_section_home_block();
		register_slider_block();
		register_stat_counter_block();
		register_sutori_embed_block();
		register_term_list_block();
		register_tickcounter_embed_block();
		register_tweet_action_block();
		register_video_block();
	}
}

add_action( 'init', 'amnesty_register_php_rendered_blocks' );
