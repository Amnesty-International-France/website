<?php

declare(strict_types=1);

if (!function_exists('render_video_block')) {
	/**
	 * Render the Amnesty Video block
	 *
	 * @package Amnesty\Blocks
	 *
	 * @param array<string, mixed> $attributes Block attributes
	 *
	 * @return string
	 */
	function render_video_block(array $attributes): string
	{

		$video_url = $attributes['url'] ?? '';
		$video_title = $attributes['title'] ?? '';

		if (empty($video_url)) {
			return '<p>' . esc_html__('Aucune vidéo sélectionnée', 'amnesty') . '</p>';
		}

		ob_start();
		?>

		<div class="video-block">
			<div class="video-wrapper">
				<iframe width="100%" src="<?php echo esc_url(str_replace('watch?v=', 'embed/', $video_url)); ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
			</div>
			<?php if (!empty($video_title)): ?>
				<p class="video-title"><span class="video-label">Vidéo : </span><?php echo esc_html($video_title); ?></p>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}
}
