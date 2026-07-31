<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (!function_exists('trailingslashit')) {
    function trailingslashit(string $value): string
    {
        return rtrim($value, '/') . '/';
    }
}

if (!function_exists('wp_parse_url')) {
    function wp_parse_url(string $url, int $component = -1): mixed
    {
        return -1 === $component ? parse_url($url) : parse_url($url, $component);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('get_attached_file')) {
    function get_attached_file(int $attachment_id): string|false
    {
        return $GLOBALS['__phpunit_attached_files'][$attachment_id] ?? false;
    }
}

if (!function_exists('wp_get_attachment_metadata')) {
    function wp_get_attachment_metadata(int $attachment_id): array|false
    {
        return $GLOBALS['__phpunit_attachment_metadata'][$attachment_id] ?? false;
    }
}

if (!function_exists('wp_get_upload_dir')) {
    function wp_get_upload_dir(): array
    {
        return $GLOBALS['__phpunit_upload_dir'];
    }
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/helpers/modern-images.php';

final class ModernImagesTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagewebp') || !function_exists('imageavif')) {
            self::markTestSkipped('GD WebP/AVIF support is required for modern image conversion tests.');
        }

        $this->tmpDir = sys_get_temp_dir() . '/amnesty-modern-images-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir . '/2026/07', 0777, true);

        $GLOBALS['__phpunit_attached_files'] = [];
        $GLOBALS['__phpunit_attachment_metadata'] = [];
        $GLOBALS['__phpunit_upload_dir'] = [
            'basedir' => $this->tmpDir,
            'baseurl' => 'https://example.test/wp-content/uploads',
            'error' => false,
        ];
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tmpDir);
    }

    public function testGeneratesAvifAndWebpVariantsForJpgAndPngAttachments(): void
    {
        $jpg = $this->createJpeg('2026/07/source.jpg', 800, 500);
        $png = $this->createPng('2026/07/source.png', 640, 400);

        $jpgMetadata = [
            'file' => '2026/07/source.jpg',
            'width' => 800,
            'height' => 500,
            'sizes' => [],
        ];
        $pngMetadata = [
            'file' => '2026/07/source.png',
            'width' => 640,
            'height' => 400,
            'sizes' => [],
        ];

        $GLOBALS['__phpunit_attached_files'][10] = $jpg;
        $GLOBALS['__phpunit_attached_files'][11] = $png;

        $jpgResult = amnesty_generate_modern_image_variants(10, $jpgMetadata, true);
        $pngResult = amnesty_generate_modern_image_variants(11, $pngMetadata, true);

        self::assertArrayHasKey('image/avif', $jpgResult[AMNESTY_MODERN_IMAGE_METADATA_KEY]['2026/07/source.jpg']);
        self::assertArrayHasKey('image/webp', $jpgResult[AMNESTY_MODERN_IMAGE_METADATA_KEY]['2026/07/source.jpg']);
        self::assertArrayHasKey('image/avif', $pngResult[AMNESTY_MODERN_IMAGE_METADATA_KEY]['2026/07/source.png']);
        self::assertArrayHasKey('image/webp', $pngResult[AMNESTY_MODERN_IMAGE_METADATA_KEY]['2026/07/source.png']);

        self::assertFileExists($this->tmpDir . '/2026/07/source.avif');
        self::assertFileExists($this->tmpDir . '/2026/07/source.webp');
    }

    public function testSkipsExistingVariantWhenItIsLargerThanSource(): void
    {
        $tiny = $this->createPng('2026/07/tiny.png', 1, 1);
        file_put_contents($this->tmpDir . '/2026/07/tiny.webp', str_repeat('x', filesize($tiny) + 100));

        $variant = amnesty_generate_modern_image_variant(
            $tiny,
            '2026/07/tiny.png',
            'image/webp',
            [ 'extension' => 'webp', 'quality' => 82 ],
            false
        );

        self::assertNull($variant);
    }

    public function testWrapsAttachmentImageWithAvifWebpSourcesAndKeepsFallbackImage(): void
    {
        $GLOBALS['__phpunit_attachment_metadata'][20] = [
            'file' => '2026/07/source.jpg',
            AMNESTY_MODERN_IMAGE_METADATA_KEY => [
                '2026/07/source.jpg' => [
                    'image/avif' => [ 'file' => '2026/07/source.avif', 'filesize' => 100 ],
                    'image/webp' => [ 'file' => '2026/07/source.webp', 'filesize' => 120 ],
                ],
            ],
        ];

        $html = '<img width="800" height="500" src="https://example.test/wp-content/uploads/2026/07/source.jpg" alt="Ada" loading="lazy" decoding="async" />';

        $picture = amnesty_picture_from_attachment_html($html, 20);

        self::assertStringStartsWith('<picture><source type="image/avif"', $picture);
        self::assertStringContainsString('<source type="image/webp"', $picture);
        self::assertStringContainsString($html, $picture);
        self::assertLessThan(
            strpos($picture, 'type="image/webp"'),
            strpos($picture, 'type="image/avif"')
        );
    }

    private function createJpeg(string $relative, int $width, int $height): string
    {
        $file = $this->tmpDir . '/' . $relative;
        $image = $this->image($width, $height);
        imagejpeg($image, $file, 100);

        return $file;
    }

    private function createPng(string $relative, int $width, int $height): string
    {
        $file = $this->tmpDir . '/' . $relative;
        $image = $this->image($width, $height);
        imagepng($image, $file, 0);

        return $file;
    }

    private function image(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorallocate($image, ($x * 13) % 255, ($y * 17) % 255, (($x + $y) * 7) % 255);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        return $image;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $dir . '/' . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
