<?php

declare(strict_types=1);

if (!function_exists('render_quote_block')) {
	/**
	 * Render the Quote block
	 *
	 * @param array<string, mixed> $attributes Block attributes
	 *
	 * @return string
	 */
	function render_quote_block(array $attributes): string {
		$quote_text = $attributes['quoteText'] ?? '';
		$author     = $attributes['author'] ?? '';
		$show_image = $attributes['showImage'] ?? false;
		$bg_color   = $attributes['bgColor'] ?? 'black';
		$size       = $attributes['size'] ?? 'medium';
        $image_id = $attributes['imageId'] ?? null;
        $image_url = $image_id ? wp_get_attachment_image_url((int) $image_id, 'large') : '';

		ob_start();
		?>
		<div class="wp-block-amnesty-core-quote-block quote-block">
            <?php if ($show_image && $image_url): ?>
                <div class="quote-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php esc_attr_e('Image de la citation', 'amnesty'); ?>" />
                </div>
            <?php endif; ?>

			<div class="quote-content <?php echo esc_attr($bg_color); ?>">
				<blockquote class="text <?php echo esc_attr($size); ?>">
					<?php echo esc_html($quote_text); ?>
				</blockquote>
				<p class="author <?php echo esc_attr($size); ?>">
					<?php echo esc_html($author); ?>
				</p>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
