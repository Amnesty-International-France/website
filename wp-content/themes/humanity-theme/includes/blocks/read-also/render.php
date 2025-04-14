<?php

declare(strict_types=1);

if (!function_exists('render_read_also_block')) {
	/**
	 * Render the Read Also block dynamically
	 *
	 * @param array<string, mixed> $attributes
	 * @return string
	 */
	function render_read_also_block(array $attributes): string {
		if (empty($attributes['postId'])) {
			return '<p>' . esc_html__('Aucun article sélectionné', 'amnesty') . '</p>';
		}

		$post_id = (int) $attributes['postId'];
		$post = get_post($post_id);

		if (!$post) {
			return '<p>' . esc_html__('Article introuvable', 'amnesty') . '</p>';
		}

		$title = get_the_title($post_id);
		$permalink = get_permalink($post_id);

		ob_start();
		?>

		<div class="read-also-block">
			<p>
				<?php esc_html_e('À lire aussi', 'amnesty'); ?> :
				<a href="<?php echo esc_url($permalink); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html($title); ?>
				</a>
			</p>
		</div>

		<?php
		return ob_get_clean();
	}
}
