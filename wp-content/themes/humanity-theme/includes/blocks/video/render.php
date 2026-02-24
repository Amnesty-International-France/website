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
        $image_broken = get_template_directory_uri() . '/assets/images/broken-video.png';

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
				<div class="warning">
					<img src="<?= $image_broken ?>" alt="warning">
					<p>
						Le visionnage de cette vidéo entraîne un dépôt de cookies de la part de YouTube. Si vous
						souhaitez lire la vidéo, vous devez consentir aux cookies pour une publicité ciblée en
						cliquant sur le bouton ci-dessous.
					</p>
					<a onclick="enableTargeting();">Paramètres des cookies</a>
				</div>

				<script type="text/plain" class="optanon-category-C0004">
					document.querySelectorAll(".warning").forEach((p) => p.style.display = "none");
				</script>

				<iframe width="100%"
						class="optanon-category-C0004"
						data-src="<?php echo esc_url($embed_url); ?>"
						title="<?php echo esc_attr($video_title); ?>"
						frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowfullscreen>
				</iframe>

				<script>
					function enableTargeting() {
						OneTrust.UpdateConsent("Category","C0004:1");
					}
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
