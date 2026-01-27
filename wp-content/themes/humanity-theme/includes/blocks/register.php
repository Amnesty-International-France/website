<?php

declare(strict_types=1);

add_filter('big_bite_block_tabbed_content_show_tab_id_settings', '__return_true');

if (! function_exists('register_block_type')) {
    return;
}

require_once __DIR__ . '/action/register.php';
require_once __DIR__ . '/action/render.php';
require_once __DIR__ . '/actions-homepage/register.php';
require_once __DIR__ . '/actions-homepage/render.php';
require_once __DIR__ . '/agenda-homepage/register.php';
require_once __DIR__ . '/agenda-homepage/render.php';
require_once __DIR__ . '/agir-legacy/register.php';
require_once __DIR__ . '/agir-legacy/render.php';
require_once __DIR__ . '/chronicle-card/register.php';
require_once __DIR__ . '/chronicle-card/render.php';
require_once __DIR__ . '/article-card/register.php';
require_once __DIR__ . '/article-card/render.php';
require_once __DIR__ . '/articles-homepage/register.php';
require_once __DIR__ . '/articles-homepage/render.php';
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
require_once __DIR__ . '/content-callout/register.php';
require_once __DIR__ . '/content-callout/render.php';
require_once __DIR__ . '/document-card/register.php';
require_once __DIR__ . '/document-card/render.php';
require_once __DIR__ . '/donation-calculator/register.php';
require_once __DIR__ . '/donation-calculator/render.php';
require_once __DIR__ . '/download-go-further/register.php';
require_once __DIR__ . '/download-go-further/render.php';
require_once __DIR__ . '/edh-card/register.php';
require_once __DIR__ . '/edh-card/render.php';
require_once __DIR__ . '/event-card/register.php';
require_once __DIR__ . '/event-card/render.php';
require_once __DIR__ . '/get-informed/register.php';
require_once __DIR__ . '/get-informed/render.php';
require_once __DIR__ . '/hero/helpers.php';
require_once __DIR__ . '/hero/register.php';
require_once __DIR__ . '/hero/render.php';
require_once __DIR__ . '/hero-large/register.php';
require_once __DIR__ . '/hero-large/render.php';
require_once __DIR__ . '/hero-homepage/register.php';
require_once __DIR__ . '/hero-homepage/render.php';
require_once __DIR__ . '/image/register.php';
require_once __DIR__ . '/image/render.php';
require_once __DIR__ . '/key-figure/register.php';
require_once __DIR__ . '/latest-chronicle-promo/register.php';
require_once __DIR__ . '/latest-chronicle-promo/render.php';
require_once __DIR__ . '/link-icon/register.php';
require_once __DIR__ . '/menu/register.php';
require_once __DIR__ . '/menu/render.php';
require_once __DIR__ . '/mission-homepage/register.php';
require_once __DIR__ . '/mission-homepage/render.php';
require_once __DIR__ . '/petition-card/register.php';
require_once __DIR__ . '/petition-card/render.php';
require_once __DIR__ . '/petition-list/register.php';
require_once __DIR__ . '/petition-list/render.php';
require_once __DIR__ . '/post-list/register.php';
require_once __DIR__ . '/post-list/render.php';
require_once __DIR__ . '/quote/register.php';
require_once __DIR__ . '/quote/render.php';
require_once __DIR__ . '/read-also/register.php';
require_once __DIR__ . '/read-also/render.php';
require_once __DIR__ . '/read-more/register.php';
require_once __DIR__ . '/read-more/render.php';
require_once __DIR__ . '/related-posts/register.php';
require_once __DIR__ . '/related-posts/render.php';
require_once __DIR__ . '/rubric-heading/register.php';
require_once __DIR__ . '/section/register.php';
require_once __DIR__ . '/section/render.php';
require_once __DIR__ . '/section-home/register.php';
require_once __DIR__ . '/slider/register.php';
require_once __DIR__ . '/slider/render.php';
require_once __DIR__ . '/term-list/register.php';
require_once __DIR__ . '/term-list/render.php';
require_once __DIR__ . '/training-card/register.php';
require_once __DIR__ . '/training-card/render.php';
require_once __DIR__ . '/tweet-action/register.php';
require_once __DIR__ . '/tweet-action/render.php';
require_once __DIR__ . '/urgent-register-form/render.php';
require_once __DIR__ . '/urgent-register-form/register.php';
require_once __DIR__ . '/video/register.php';
require_once __DIR__ . '/video/render.php';

if (! function_exists('amnesty_register_php_rendered_blocks')) {
    /**
     * Register the blocks that require php to be rendered.
     *
     * @package Amnesty\Blocks
     *
     * @return void
     */
    function amnesty_register_php_rendered_blocks(): void
    {
        register_action_block();
        register_actions_homepage_block();
        register_agenda_homepage_block();
        register_agir_legacy_block();
        register_article_card_block();
        register_articles_homepage_block();
        register_chronicle_card_block();
        register_button_block();
        register_call_to_action_block();
        register_carousel_block();
        register_card_image_text_block();
        register_chapo_block();
        register_chip_category_block();
        register_collapsable_block();
        register_content_callout_block();
        register_document_card_block();
        register_donation_calculator_block();
        register_download_go_further_block();
        register_edh_card_block();
        register_event_card_block();
        register_get_informed_block();
        register_hero_block();
        register_hero_homepage_block();
        register_image_block();
        register_key_figure_block();
        register_latest_chronicle_promo_block();
        register_link_icon_block();
        register_list_block();
        register_menu_block();
        register_mission_homepage_block();
        register_petition_card_block();
        register_petition_list_block();
        register_quote_block();
        register_read_also_block();
        register_read_more_block();
        register_related_posts_block();
        register_rubric_heading_block();
        register_section_block();
        register_section_home_block();
        register_slider_block();
        register_term_list_block();
        register_training_card_block();
        register_tweet_action_block();
        register_urgent_register_form_block();
        register_video_block();
    }
}

add_action('init', 'amnesty_register_php_rendered_blocks');
