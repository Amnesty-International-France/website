<?php

declare(strict_types=1);

if (!function_exists('render_video_block')) {
    /**
     * Extract the YouTube video ID from various URL formats.
     *
     * @param string $url The full YouTube URL.
     * @return string|null The extracted video ID or null if not found.
     */
    function extract_youtube_id(string $url): ?string
    {
        $parsed_url = parse_url($url);

        if (!isset($parsed_url['host'])) {
            return null;
        }

        if (strpos($parsed_url['host'], 'youtu.be') !== false && isset($parsed_url['path'])) {
            return ltrim($parsed_url['path'], '/');
        }

        if (strpos($parsed_url['host'], 'youtube.com') !== false && isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_vars);
            return $query_vars['v'] ?? null;
        }

        return null;
    }

    /**
     * Render the Amnesty Video block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string
     */
    function render_video_block(array $attributes): string
    {
        $video_url = $attributes['url'] ?? '';
        $video_title = $attributes['title'] ?? '';

        if (empty($video_url)) {
            return '<p>' . esc_html__('Aucune vidéo sélectionnée', 'amnesty') . '</p>';
        }

        $video_id = extract_youtube_id($video_url);

        if (!$video_id) {
            return '<p>' . esc_html__('Lien vidéo invalide', 'amnesty') . '</p>';
        }

        $embed_url = 'https://www.youtube.com/embed/' . esc_attr($video_id);

        ob_start();
        ?>

        <div class="video-block">
            <div class="video-wrapper">
                <iframe width="100%" data-src="<?php echo esc_url($embed_url); ?>" frameborder="0"
                        allow="autoplay; encrypted-media" allowfullscreen title="<?php echo esc_attr($video_title); ?>"></iframe>
				<script>
					document.addEventListener('DOMContentLoaded',function() {
						var iframes =document.querySelectorAll('iframe[data-src]');
						iframes.forEach(function(iframe) {
							var src = iframe.getAttribute('data-src');
							if (!src) {
								iframe.insertAdjacentHTML(
									'beforebegin',
									'<p>Veuillez accepter les cookies pour afficher ce contenu.</p>'
								);
							}
						});
					});
				</script>
            </div>
            <?php if (!empty($video_title)): ?>
                <p class="video-title">
                    <span class="video-label">Vidéo : </span><?php echo esc_html($video_title); ?>
                </p>
            <?php endif; ?>
        </div>

        <?php
        return ob_get_clean();
    }
}
