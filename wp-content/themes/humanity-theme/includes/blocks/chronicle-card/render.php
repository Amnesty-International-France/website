<?php

declare(strict_types=1);

if (!function_exists('render_chronicle_card_block')) {
	function render_chronicle_card_block(): string
	{
		$post_to_render = $GLOBALS['post'] ?? null;

		if (!$post_to_render) {
			return '';
		}

		$post_id = $post_to_render->ID;

		$permalink = get_permalink($post_to_render);
		$cover_image = get_field('cover_image', $post_id);
		$cover = null;

		if ($cover_image && is_array($cover_image)) {
			$cover = sprintf(
				'<img src="%s" alt="%s" class="chronicle__image" />',
				esc_url($cover_image['sizes']['medium']),
				esc_attr($cover_image['alt']),
			);
		}

		ob_start();
?>

<a href="<?php echo esc_url($permalink); ?>" class="chronicle__permalink">
	<?php if ($cover): ?>
		<?php echo $cover; ?>
	<?php else: ?>
		<div class="edh-card-thumbnail-placeholder"></div>
	<?php endif; ?>
</a>

<?php
		return ob_get_clean();
	}
}
