<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

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

    function amnesty_get_attachment_picture(int $attachment_id, string $size = 'thumbnail', array $attr = []): string
    {
        $GLOBALS['__phpunit_rendered_attachment_pictures'][] = [
            'attachment_id' => $attachment_id,
            'size' => $size,
            'attr' => $attr,
        ];

        return sprintf(
            '<picture data-attachment-id="%d" data-size="%s"><img alt="%s"></picture>',
            $attachment_id,
            esc_attr($size),
            esc_attr((string) ($attr['alt'] ?? ''))
        );
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/blocks/image/render.php';

final class ImageBlockTest extends TestCase
{
    protected function setUp(): void
    {
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

        $GLOBALS['__phpunit_rendered_attachment_pictures'] = [];
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

    public function testRendersMobileOnlyLegacyImageBlock(): void
    {
        $html = render_image_block([ 'mediaMobileId' => 20 ]);

        self::assertStringContainsString('data-attachment-id="20"', $html);
        self::assertStringContainsString('alt="Mobile alt"', $html);
        self::assertStringContainsString('<p class="image-caption">Mobile caption</p>', $html);
        self::assertStringContainsString('<p class="image-description"><em>Mobile credit</em></p>', $html);
    }

    public function testRendersDesktopAndMobileImagesWithDeviceWrappers(): void
    {
        $html = render_image_block([ 'mediaId' => 10, 'mediaMobileId' => 20, 'fullWidth' => true ]);

        self::assertStringContainsString('image-fullwidth', $html);
        self::assertStringContainsString('image-device-desktop', $html);
        self::assertStringContainsString('image-device-mobile', $html);
        self::assertSame([10, 20], array_column($GLOBALS['__phpunit_rendered_attachment_pictures'], 'attachment_id'));
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
