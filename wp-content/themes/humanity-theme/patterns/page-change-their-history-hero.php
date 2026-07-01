<?php
/**
 * Title: Page Change Their History Hero
 * Description: Outputs the Change Their History page hero.
 * Slug: amnesty/page-change-their-history-hero
 * Inserter: no
 */

declare(strict_types=1);

$page_id = (int) get_the_ID();
$hero_title = get_field('change_their_history_hero_title', $page_id);
$hero_description = get_field('change_their_history_hero_description', $page_id);
$hero_button_label = get_field('change_their_history_hero_button_label', $page_id);
$active_campaign = function_exists('amnesty_get_active_clh_campaign_for_page')
    ? amnesty_get_active_clh_campaign_for_page($page_id)
    : null;
$hero_button_url = ($active_campaign && function_exists('amnesty_get_clh_petition_tunnel_url'))
    ? amnesty_get_clh_petition_tunnel_url()
    : '';


if (! $hero_title) {
    $hero_title = get_the_title();
}
?>

<section class="page-change-their-history-hero">
    <div class="page-change-their-history-hero-content">
        <?php if ($hero_title) : ?>
            <h1 class="page-change-their-history-hero-content-title"><?php echo esc_html($hero_title); ?></h1>
        <?php endif; ?>
        <?php if ($hero_description) : ?>
            <p class="page-change-their-history-hero-content-description"><?php echo esc_html($hero_description); ?></p>
        <?php endif; ?>
        <?php if ($hero_button_label && $hero_button_url) : ?>
            <div class='custom-button-block center'>
                <a href="<?php echo esc_url($hero_button_url); ?>" class="custom-button">
                    <div class='content outline-yellow medium'>
                        <div class="icon-container">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth="1.5"
                                stroke="currentColor"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                        <div class="button-label"><?php echo esc_html($hero_button_label); ?></div>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
if (! is_admin()) {
    add_filter('the_content', 'amnesty_remove_first_hero_from_content', 0);
}
?>
