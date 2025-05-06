<?php

declare(strict_types=1);

if (!function_exists('render_agir_legacy_block')) {
	/**
	 * Render the Agir Legacy block dynamically
	 *
	 * @param array<string, mixed> $attributes
	 * @return string
	 */
	function render_agir_legacy_block(array $attributes): string {
		$link_type = $attributes['linkType'] ?? 'internal';
		$html      = '<div class="read-also-block"><p>' . esc_html__('Agir', 'amnesty') . ' : ';

		if ($link_type === 'external') {
			$url   = esc_url($attributes['externalUrl'] ?? '');
			$label = esc_html($attributes['externalLabel'] ?? $url);

			if ($url) {
				$html .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
			} else {
				$html .= '<span>' . esc_html__('Aucun lien externe fourni.', 'amnesty') . '</span>';
			}
		} else {
			if (empty($attributes['postId'])) {
				$html .= '<span>' . esc_html__('Aucun article sélectionné.', 'amnesty') . '</span>';
			} else {
				$post_id = (int) $attributes['postId'];
				$post    = get_post($post_id);

				if (!$post) {
					$html .= esc_html__('Article introuvable.', 'amnesty') ;
				} else {
					$title     = get_the_title($post_id);
					$permalink = get_permalink($post_id);

					$html .= '<a href="' . esc_url($permalink) . '" target="_blank" rel="noopener noreferrer">'
						. esc_html($title) . '</a>';
				}
			}
		}

		$html .= '</p></div>';
		return $html;
	}
}
