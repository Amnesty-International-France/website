<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * The theme keeps uploaded originals at quality 100 (see amnesty_image_quality).
 * Photon mirrors that quality, and at 100 libwebp switches to lossless, which
 * made the WebP it served heavier than the JPEG it replaced. These tests cover
 * the arguments we hand Photon to keep it on the lossy encoder.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PhotonDeliveryArgsTest extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('add_filter')) {
            function add_filter(string $hook, callable|string $callback, int $priority = 10, int $accepted_args = 1): bool
            {
                return true;
            }
        }

        if (!function_exists('add_action')) {
            function add_action(string $hook, callable|string $callback, int $priority = 10, int $accepted_args = 1): bool
            {
                return true;
            }
        }

        if (!function_exists('remove_filter')) {
            function remove_filter(string $hook, callable|string $callback, int $priority = 10): bool
            {
                return true;
            }
        }

        if (!function_exists('add_image_size')) {
            function add_image_size(string $name, int $width = 0, int $height = 0, bool|array $crop = false): void
            {
            }
        }

        if (!function_exists('wp_parse_url')) {
            function wp_parse_url(string $url, int $component = -1): mixed
            {
                return parse_url($url, $component);
            }
        }

        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/helpers/media.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/theme-setup/media.php';
    }

    public function testPinsQualityOnJpegSources(): void
    {
        $args = amnesty_photon_delivery_args(
            [ 'resize' => '1024,681' ],
            'https://www.amnesty.fr/wp-content/uploads/2026/06/photo.jpg'
        );

        self::assertSame(
            [
                'resize' => '1024,681',
                'quality' => AMNESTY_PHOTON_DELIVERY_QUALITY,
            ],
            $args
        );
    }

    /**
     * Photon serves PNG sources as lossless WebP, which is what logos and line
     * art need. Pinning a quality would push them through the lossy encoder.
     */
    public function testLeavesPngSourcesOnTheLosslessEncoder(): void
    {
        $args = amnesty_photon_delivery_args(
            [ 'resize' => '380,144' ],
            'https://www.amnesty.fr/wp-content/uploads/2025/05/logo-scaled.png'
        );

        self::assertSame([ 'resize' => '380,144' ], $args);
    }

    public function testIgnoresQueryStringsWhenReadingTheExtension(): void
    {
        $args = amnesty_photon_delivery_args(
            [],
            'https://www.amnesty.fr/wp-content/uploads/2026/06/photo.jpeg?v=2'
        );

        self::assertSame([ 'quality' => AMNESTY_PHOTON_DELIVERY_QUALITY ], $args);
    }

    public function testLeavesAnUnknownExtensionAlone(): void
    {
        self::assertSame([], amnesty_photon_delivery_args([], 'https://www.amnesty.fr/logo.svg'));
    }

    public function testKeepsQualityBelowTheLosslessThreshold(): void
    {
        self::assertLessThan(100, AMNESTY_PHOTON_DELIVERY_QUALITY);
    }

    public function testDoesNotOverrideAnExplicitQuality(): void
    {
        $args = amnesty_photon_delivery_args(
            [ 'quality' => 60 ],
            'https://www.amnesty.fr/wp-content/uploads/2026/06/photo.jpg'
        );

        self::assertSame([ 'quality' => 60 ], $args);
    }

    public function testLeavesStringArgumentsUntouched(): void
    {
        self::assertSame(
            'w=1024',
            amnesty_photon_delivery_args('w=1024', 'https://www.amnesty.fr/wp-content/uploads/2026/06/photo.jpg')
        );
    }
}
