<?php

declare(strict_types=1);

if (!function_exists('render_hero_homepage_block')) {
    /**
     * Render the Hero Homepage block
     *
     * @param array<string, mixed> $attributes
     *
     * @return string
     */
    function render_hero_homepage_block(array $attributes): string
    {
        $items = $attributes['items'] ?? [];

        if (empty($items) || !is_array($items)) {
            return '';
        }

        $item = $items[array_rand($items)];

        $media_id     = (int) ($item['mediaId'] ?? 0);
        $mobile_media_id = (int) ($item['mediaMobileId'] ?? 0);

        $image_url    = $media_id ? wp_get_attachment_image_url($media_id, 'full') : null;
        $image_alt    = $media_id ? get_post_meta($media_id, '_wp_attachment_image_alt', true) : null;
        $mobile_image_url = $mobile_media_id ? wp_get_attachment_image_url($mobile_media_id, 'full') : null;
        $mobile_image_alt = $mobile_media_id ? get_post_meta($mobile_media_id, '_wp_attachment_image_alt', true) : null;

        $subtitle     = esc_html($item['subtitle'] ?? '');
        $button_label = esc_html($item['buttonLabel'] ?? '');
        $button_url   = esc_url($item['buttonUrl'] ?? '');

        ob_start();
        ?>

		<div class="hero-homepage">
			<div class="item">
				<div class="hero-wrapper">
					<?php if (isset($image_url) || isset($mobile_image_url)): ?>
						<div class="hero-image-wrapper">
							<img class="hero-image" src="<?php echo esc_url($image_url ?? $mobile_image_url ?? ''); ?>" alt="<?php echo esc_attr($image_alt ?? $mobile_image_alt ?? ''); ?>">
						</div>
						<div class="hero-image-mobile-wrapper">
							<img class="hero-image" src="<?php echo esc_url($mobile_image_url ?? $image_url ?? ''); ?>" alt="<?php echo esc_attr($mobile_image_alt ?? $image_alt ?? ''); ?>">
						</div>
						<div class="hero-content-wrapper">
							<h1 class="hero-title">
								On se bat ensemble,<br />on gagne ensemble.
							</h1>
							<?php if ($subtitle): ?>
								<h2 class="hero-subtitle"><?php echo esc_html($subtitle); ?></h2>
							<?php endif; ?>
							<div class='custom-button-block center'>
								<a href="<?php echo esc_url($button_url); ?>" <?php if (! is_internal_link($button_url)) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?> class="custom-button">
									<div class='content bg-yellow medium'>
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
										<div class="button-label"><?php echo esc_html($button_label); ?></div>
									</div>
								</a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
        return ob_get_clean();
    }
}
