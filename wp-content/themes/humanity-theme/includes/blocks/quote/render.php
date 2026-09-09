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
    function render_quote_block(array $attributes): string
    {
        $quote_text = $attributes['quoteText'] ?? '';
        $author     = $attributes['author'] ?? '';
        $show_image = $attributes['showImage'] ?? false;
        $bg_color   = $attributes['bgColor'] ?? 'black';
        $size       = $attributes['size'] ?? 'medium';
        $image_id = $attributes['imageId'] ?? null;

        ob_start();
        ?>
		<div class="wp-block-amnesty-core-quote-block quote-block">
            <?php if ($show_image && $image_id): ?>
                <div class="quote-image">
                    <?php echo wp_get_attachment_image((int) $image_id, 'large', false, [ 'alt' => esc_attr__('Image de la citation', 'amnesty'), 'loading' => 'lazy', 'decoding' => 'async' ]); ?>
                </div>
            <?php endif; ?>

			<div class="quote-content <?php echo esc_attr($bg_color); ?>">
				<blockquote class="text <?php echo esc_attr($size); ?>">
					<?php echo esc_html($quote_text); ?>
				</blockquote>
				<?php if (!empty($author)): ?>
                    <p class="author <?php echo esc_attr($size); ?>">
                        <?php echo esc_html($author); ?>
                    </p>
                <?php endif; ?>
			</div>
		</div>
		<?php

        return ob_get_clean();
    }
}
