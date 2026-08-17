<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $text): string
    {
        return str_starts_with($text, 'javascript:') ? '' : htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post(string $text): string
    {
        return $text;
    }
}

if (!function_exists('get_post_meta')) {
    $GLOBALS['__phpunit_post_meta'] = [];

    function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed
    {
        $value = $GLOBALS['__phpunit_post_meta'][$post_id][$key] ?? '';

        return $single ? $value : [$value];
    }
}

if (!function_exists('amnesty_get_attachment_picture')) {
    $GLOBALS['__phpunit_rendered_attachment_pictures'] = [];
    $GLOBALS['__phpunit_modern_picture_sources'] = [];

    function amnesty_get_attachment_picture(int $attachment_id, string $size = 'thumbnail', array $attr = []): string
    {
        $GLOBALS['__phpunit_rendered_attachment_pictures'][] = [
            'attachment_id' => $attachment_id,
            'size' => $size,
            'attr' => $attr,
        ];

        $src = function_exists('wp_get_attachment_image_src') ? wp_get_attachment_image_src($attachment_id, $size) : false;
        $attributes = [
            'src' => is_array($src) ? (string) ($src[0] ?? '') : '',
            'alt' => (string) ($attr['alt'] ?? ''),
        ];

        if (is_array($src) && (int) ($src[1] ?? 0) > 0) {
            $attributes['width'] = (string) $src[1];
        }

        if (is_array($src) && (int) ($src[2] ?? 0) > 0) {
            $attributes['height'] = (string) $src[2];
        }

        $attributes = array_merge($attributes, $attr);
        $html_attributes = '';

        foreach ($attributes as $name => $value) {
            if ('alt' !== $name && '' === (string) $value) {
                continue;
            }

            $html_attributes .= sprintf(
                ' %s="%s"',
                esc_attr((string) $name),
                'src' === $name ? esc_url((string) $value) : esc_attr((string) $value)
            );
        }

        return sprintf(
            '<picture data-attachment-id="%d" data-size="%s">%s<img%s></picture>',
            $attachment_id,
            esc_attr($size),
            $GLOBALS['__phpunit_modern_picture_sources'][$attachment_id] ?? '',
            $html_attributes
        );
    }
}

if (!function_exists('wp_get_attachment_image_src')) {
    $GLOBALS['__phpunit_attachment_image_sources'] = [];

    function wp_get_attachment_image_src(int $attachment_id, string|array $size = 'thumbnail'): array|false
    {
        if (isset($GLOBALS['__phpunit_attachment_image_sources'][$attachment_id])) {
            return $GLOBALS['__phpunit_attachment_image_sources'][$attachment_id];
        }

        return [
            sprintf('image-%d.jpg', $attachment_id),
            1000,
            700,
        ];
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ImageBlockTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/blocks/image/render.php';

        $GLOBALS['__phpunit_posts'] = [
            10 => (object) [
                'ID' => 10,
                'post_excerpt' => 'Desktop caption',
                'post_content' => '<strong>Desktop credit</strong>',
            ],
            20 => (object) [
                'ID' => 20,
                'post_excerpt' => 'Mobile caption',
                'post_content' => '<em>Mobile credit</em>',
            ],
        ];

        $GLOBALS['__phpunit_post_meta'] = [
            10 => [ '_wp_attachment_image_alt' => 'Desktop alt' ],
            20 => [ '_wp_attachment_image_alt' => 'Mobile alt' ],
        ];

        $GLOBALS['__phpunit_attachment_image_sources'] = [];
        $GLOBALS['__phpunit_rendered_attachment_pictures'] = [];
        $GLOBALS['__phpunit_modern_picture_sources'] = [];
    }

    public function testRendersLegacyDesktopImageBlockWithCaptionAndDescription(): void
    {
        $html = render_image_block([ 'mediaId' => 10 ]);

        self::assertStringContainsString('class="image-block ', $html);
        self::assertStringContainsString('data-attachment-id="10"', $html);
        self::assertStringContainsString('alt="Desktop alt"', $html);
        self::assertStringContainsString('<p class="image-caption">Desktop caption</p>', $html);
        self::assertStringContainsString('<p class="image-description"><strong>Desktop credit</strong></p>', $html);
    }

    public function testRendersMobileOnlyImageBlockInASingleWrapper(): void
    {
        $html = render_image_block([ 'mediaMobileId' => 20 ]);

        self::assertStringContainsString('data-attachment-id="20"', $html);
        self::assertStringContainsString('alt="Mobile alt"', $html);
        self::assertSame(1, substr_count($html, 'class="image-wrapper"'));
        self::assertStringNotContainsString('image-device-desktop', $html);
        self::assertStringNotContainsString('image-device-mobile', $html);
        self::assertStringContainsString('<p class="image-caption">Mobile caption</p>', $html);
        self::assertStringContainsString('<p class="image-description"><em>Mobile credit</em></p>', $html);
    }

    public function testRendersDesktopAndMobileImagesInASingleResponsivePicture(): void
    {
        $html = render_image_block([ 'mediaId' => 10, 'mediaMobileId' => 20, 'fullWidth' => true ]);

        self::assertStringContainsString('image-fullwidth', $html);
        self::assertSame(1, substr_count($html, 'class="image-wrapper"'));
        self::assertSame(1, substr_count($html, '<picture'));
        self::assertStringContainsString('<source media="(min-width: 640px)" srcset="image-10.jpg" />', $html);
        self::assertStringContainsString('<img src="image-20.jpg"', $html);
        self::assertStringContainsString('alt="Desktop alt"', $html);
        self::assertStringContainsString('width="1000"', $html);
        self::assertStringContainsString('height="700"', $html);
        self::assertStringNotContainsString('image-device-desktop', $html);
        self::assertStringNotContainsString('image-device-mobile', $html);
        self::assertSame(1, substr_count($html, '<p class="image-caption">Desktop caption</p>'));
    }

    public function testResponsivePictureKeepsModernImageSources(): void
    {
        $GLOBALS['__phpunit_modern_picture_sources'] = [
            10 => '<source type="image/avif" srcset="desktop.avif" /><source type="image/webp" srcset="desktop.webp" />',
            20 => '<source type="image/avif" srcset="mobile.avif" /><source type="image/webp" srcset="mobile.webp" />',
        ];

        $html = render_image_block([ 'mediaId' => 10, 'mediaMobileId' => 20 ]);

        self::assertStringContainsString('<source media="(min-width: 640px)" type="image/avif" srcset="desktop.avif" />', $html);
        self::assertStringContainsString('<source media="(min-width: 640px)" type="image/webp" srcset="desktop.webp" />', $html);
        self::assertStringContainsString('<source media="(min-width: 640px)" srcset="image-10.jpg" />', $html);
        self::assertStringContainsString('<source type="image/avif" srcset="mobile.avif" />', $html);
        self::assertStringContainsString('<source type="image/webp" srcset="mobile.webp" />', $html);
    }

    public function testResponsivePictureKeepsEmptyAltAndOmitsInvalidDimensions(): void
    {
        $GLOBALS['__phpunit_post_meta'] = [
            10 => [ '_wp_attachment_image_alt' => '' ],
            20 => [ '_wp_attachment_image_alt' => '' ],
        ];
        $GLOBALS['__phpunit_attachment_image_sources'] = [
            10 => [ 'desktop.svg', 0, 0 ],
            20 => [ 'mobile.svg', 0, 0 ],
        ];

        $html = render_image_block([ 'mediaId' => 10, 'mediaMobileId' => 20 ]);

        self::assertStringContainsString('<picture>', $html);
        self::assertStringContainsString('<img src="mobile.svg" alt="" loading="lazy" decoding="async">', $html);
        self::assertStringNotContainsString('width="0"', $html);
        self::assertStringNotContainsString('height="0"', $html);
        self::assertStringNotContainsString('aspect-ratio:', $html);
    }

    public function testResponsivePictureSetsDeviceAspectRatios(): void
    {
        $GLOBALS['__phpunit_attachment_image_sources'] = [
            10 => [ 'desktop-wide.jpg', 1200, 600 ],
            20 => [ 'mobile-tall.jpg', 400, 500 ],
        ];

        $html = render_image_block([ 'mediaId' => 10, 'mediaMobileId' => 20 ]);

        self::assertStringContainsString(
            '<picture style="--image-mobile-aspect-ratio: 400 / 500; --image-desktop-aspect-ratio: 1200 / 600;">',
            $html
        );
        self::assertStringContainsString('<source media="(min-width: 640px)" srcset="desktop-wide.jpg" />', $html);
        self::assertStringContainsString('<img src="mobile-tall.jpg"', $html);
        self::assertStringContainsString('width="400"', $html);
        self::assertStringContainsString('height="500"', $html);
    }

    public function testSimpleStyleKeepsImageAndSuppressesMetadata(): void
    {
        $html = render_image_block([ 'mediaId' => 10, 'className' => 'is-style-simple' ]);

        self::assertStringContainsString('data-attachment-id="10"', $html);
        self::assertStringNotContainsString('image-caption', $html);
        self::assertStringNotContainsString('image-description', $html);
    }

    public function testEmptyLegacyBlockShowsEditorFallbackMessage(): void
    {
        self::assertSame('<p>Aucune image sélectionnée</p>', render_image_block([]));
    }
}
