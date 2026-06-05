<?php

/**
 * Title: Page Hero Tunnel CLH
 * Description: Outputs the page's tunnel clh hero, if any
 * Slug: amnesty/page-hero-tunnel-clh
 * Inserter: no
 */

declare(strict_types=1);

$featured_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
$page_title         = get_the_title();
$over_title = get_field('sur-titre', get_the_ID()) ?? '';
$clh_tunnel_context = function_exists('amnesty_get_clh_tunnel_context') ? amnesty_get_clh_tunnel_context() : [];
$clh_signed_count = (int) ($clh_tunnel_context['signed_count'] ?? 0);
$clh_total_steps = min(10, count($clh_tunnel_context['list_petitions_clh'] ?? []));
$is_clh_tunnel_thanks_request = function_exists('amnesty_is_clh_tunnel_thanks_request') && amnesty_is_clh_tunnel_thanks_request();

?>

<?php if (! is_front_page()) : ?>
	<section class="page-hero-block page-tunnel-clh-hero">
		<!-- wp:pattern {"slug":"amnesty/featured-image"} /-->
		<div class="yoast-breadcrumb-wrapper">
			<?php
            if (function_exists('yoast_breadcrumb')) {
                yoast_breadcrumb('<nav class="yoast-breadcrumb">', '</nav>');
            }
    ?>
		</div>
		<div class="page-hero-title-wrapper">
			<div class="container">
				<div class="container-title">
					<?php if ($over_title) : ?>
						<h1 class="page-hero-overtitle"><?php echo esc_html($over_title); ?></h1><br/>
					<?php endif; ?>
					<span class="page-hero-title"><?php echo esc_html($page_title); ?></span>
					<?php if (! $is_clh_tunnel_thanks_request) : ?>
					<?php
                    get_template_part('partials/tunnel-clh-stepper', null, [
                        'signed_count' => $clh_signed_count,
                        'total_steps' => $clh_total_steps,
                        'modifier_class' => 'tunnel-clh-stepper--mobile',
                    ]);
    ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>


<?php
if (! is_admin()) {
    add_filter('the_content', 'amnesty_remove_first_hero_from_content', 0);
}
