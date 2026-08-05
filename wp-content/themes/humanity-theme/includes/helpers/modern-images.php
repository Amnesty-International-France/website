<?php

declare(strict_types=1);

if (! defined('AMNESTY_MODERN_IMAGE_METADATA_KEY')) {
    define('AMNESTY_MODERN_IMAGE_METADATA_KEY', 'amnesty_modern_formats');
}

if (! function_exists('amnesty_modern_image_formats')) {
    /**
     * @return array<string,array{extension:string,quality:int}>
     */
    function amnesty_modern_image_formats(): array
    {
        return [
            'image/avif' => [
                'extension' => 'avif',
                'quality'   => 55,
            ],
            'image/webp' => [
                'extension' => 'webp',
                'quality'   => 82,
            ],
        ];
    }
}

if (! function_exists('amnesty_modern_image_source_is_supported')) {
    function amnesty_modern_image_source_is_supported(string $file): bool
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($extension, [ 'jpg', 'jpeg', 'jfif', 'png' ], true);
    }
}

if (! function_exists('amnesty_modern_image_editor_supports')) {
    function amnesty_modern_image_editor_supports(string $mime_type): bool
    {
        if (function_exists('wp_image_editor_supports')) {
            return wp_image_editor_supports([ 'mime_type' => $mime_type ]);
        }

        return match ($mime_type) {
            'image/avif' => function_exists('imageavif'),
            'image/webp' => function_exists('imagewebp'),
            default => false,
        };
    }
}

if (! function_exists('amnesty_modern_image_relative_dir')) {
    function amnesty_modern_image_relative_dir(array $metadata): string
    {
        $file = (string) ($metadata['file'] ?? '');
        $dir  = str_replace('\\', '/', dirname($file));

        return '.' === $dir ? '' : trim($dir, '/');
    }
}

if (! function_exists('amnesty_modern_image_candidates')) {
    /**
     * @return array<string,string> Map of relative upload file to absolute file path.
     */
    function amnesty_modern_image_candidates(array $metadata, string $attached_file): array
    {
        $candidates = [];
        $base_dir   = dirname($attached_file);
        $relative_dir = amnesty_modern_image_relative_dir($metadata);

        if (! empty($metadata['file']) && is_string($metadata['file'])) {
            $candidates[str_replace('\\', '/', $metadata['file'])] = $attached_file;
        }

        foreach (($metadata['sizes'] ?? []) as $size) {
            if (! is_array($size) || empty($size['file']) || ! is_string($size['file'])) {
                continue;
            }

            $relative = ltrim(($relative_dir ? $relative_dir . '/' : '') . $size['file'], '/');
            $candidates[$relative] = $base_dir . DIRECTORY_SEPARATOR . $size['file'];
        }

        return $candidates;
    }
}

if (! function_exists('amnesty_save_modern_image_with_gd')) {
    function amnesty_save_modern_image_with_gd(string $source_file, string $destination_file, string $mime_type, int $quality): bool
    {
        $extension = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));
        $image = match ($extension) {
            'jpg', 'jpeg', 'jfif' => function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($source_file) : false,
            'png' => function_exists('imagecreatefrompng') ? imagecreatefrompng($source_file) : false,
            default => false,
        };

        if (! $image) {
            return false;
        }

        if ('png' === $extension && function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $saved = match ($mime_type) {
            'image/avif' => function_exists('imageavif') && imageavif($image, $destination_file, $quality),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, $destination_file, $quality),
            default => false,
        };

        return $saved;
    }
}

if (! function_exists('amnesty_generate_modern_image_variant')) {
    /**
     * @return array{file:string,filesize:int}|null
     */
    function amnesty_generate_modern_image_variant(string $source_file, string $relative_file, string $mime_type, array $format, bool $force = false): ?array
    {
        if (! is_file($source_file) || ! is_readable($source_file) || ! amnesty_modern_image_source_is_supported($source_file)) {
            return null;
        }

        $source_size = filesize($source_file);
        if (! $source_size) {
            return null;
        }

        $extension = (string) $format['extension'];
        $destination_file = preg_replace('/\.[^.]+$/', '.' . $extension, $source_file);
        $relative_destination = preg_replace('/\.[^.]+$/', '.' . $extension, $relative_file);

        if (! is_string($destination_file) || ! is_string($relative_destination)) {
            return null;
        }

        if (! $force && is_file($destination_file)) {
            $destination_size = filesize($destination_file);

            if ($destination_size && $destination_size < $source_size) {
                return [
                    'file'     => str_replace('\\', '/', $relative_destination),
                    'filesize' => $destination_size,
                ];
            }

            return null;
        }

        if (! amnesty_modern_image_editor_supports($mime_type)) {
            return null;
        }

        if (is_file($destination_file)) {
            unlink($destination_file);
        }

        $saved = false;
        if (function_exists('wp_get_image_editor')) {
            $editor = wp_get_image_editor($source_file);

            if (! is_wp_error($editor)) {
                if (method_exists($editor, 'set_quality')) {
                    $editor->set_quality((int) $format['quality']);
                }

                $result = $editor->save($destination_file, $mime_type);
                $saved = is_array($result) && is_file($destination_file);
            }
        }

        if (! $saved) {
            $saved = amnesty_save_modern_image_with_gd($source_file, $destination_file, $mime_type, (int) $format['quality']);
        }

        if (! $saved || ! is_file($destination_file)) {
            return null;
        }

        $destination_size = filesize($destination_file);
        if (! $destination_size || $destination_size >= $source_size) {
            unlink($destination_file);
            return null;
        }

        return [
            'file'     => str_replace('\\', '/', $relative_destination),
            'filesize' => $destination_size,
        ];
    }
}

if (! function_exists('amnesty_generate_modern_image_variants')) {
    /**
     * Generate AVIF/WebP siblings for an attachment and its registered sizes.
     *
     * @param array<string,mixed> $metadata
     *
     * @return array<string,mixed>
     */
    function amnesty_generate_modern_image_variants(int $attachment_id, array $metadata, bool $force = false): array
    {
        if (! function_exists('get_attached_file')) {
            return $metadata;
        }

        $attached_file = get_attached_file($attachment_id);
        if (! is_string($attached_file) || '' === $attached_file) {
            return $metadata;
        }

        $modern_formats = is_array($metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY] ?? null)
            ? $metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY]
            : [];

        foreach (amnesty_modern_image_candidates($metadata, $attached_file) as $relative_file => $source_file) {
            foreach (amnesty_modern_image_formats() as $mime_type => $format) {
                $variant = amnesty_generate_modern_image_variant($source_file, $relative_file, $mime_type, $format, $force);
                if (null === $variant) {
                    unset($modern_formats[$relative_file][$mime_type]);
                    continue;
                }

                $modern_formats[$relative_file][$mime_type] = $variant;
            }

            if (empty($modern_formats[$relative_file])) {
                unset($modern_formats[$relative_file]);
            }
        }

        if ($modern_formats !== []) {
            $metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY] = $modern_formats;
        } else {
            unset($metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY]);
        }

        return $metadata;
    }
}

if (! function_exists('amnesty_generate_modern_image_variants_on_metadata_update')) {
    function amnesty_generate_modern_image_variants_on_metadata_update(array $metadata, int $attachment_id): array
    {
        return amnesty_generate_modern_image_variants($attachment_id, $metadata);
    }
}

if (! function_exists('amnesty_uploads_baseurl')) {
    function amnesty_uploads_baseurl(): string
    {
        if (! function_exists('wp_get_upload_dir')) {
            return '';
        }

        $uploads = wp_get_upload_dir();
        if (! empty($uploads['error']) || empty($uploads['baseurl']) || ! is_string($uploads['baseurl'])) {
            return '';
        }

        return trailingslashit($uploads['baseurl']);
    }
}

if (! function_exists('amnesty_uploads_relative_path_from_url')) {
    function amnesty_uploads_relative_path_from_url(string $url): string
    {
        $baseurl = amnesty_uploads_baseurl();
        if ('' === $baseurl) {
            return '';
        }

        $url_path = wp_parse_url($url, PHP_URL_PATH);
        $base_path = wp_parse_url($baseurl, PHP_URL_PATH);

        if (! is_string($url_path) || ! is_string($base_path)) {
            return '';
        }

        $base_path = trailingslashit($base_path);
        if (! str_starts_with($url_path, $base_path)) {
            return '';
        }

        return ltrim(substr($url_path, strlen($base_path)), '/');
    }
}

if (! function_exists('amnesty_modern_image_url')) {
    function amnesty_modern_image_url(string $relative_file): string
    {
        $baseurl = amnesty_uploads_baseurl();

        return '' === $baseurl ? '' : $baseurl . ltrim($relative_file, '/');
    }
}

if (! function_exists('amnesty_get_modern_variant_for_url')) {
    function amnesty_get_modern_variant_for_url(int $attachment_id, string $mime_type, string $url): string
    {
        if (! function_exists('wp_get_attachment_metadata')) {
            return '';
        }

        $metadata = wp_get_attachment_metadata($attachment_id);
        if (! is_array($metadata)) {
            return '';
        }

        $relative_file = amnesty_uploads_relative_path_from_url($url);
        if ('' === $relative_file) {
            return '';
        }

        $modern_formats = $metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY] ?? [];
        if (empty($modern_formats[$relative_file][$mime_type]['file']) || ! is_string($modern_formats[$relative_file][$mime_type]['file'])) {
            return '';
        }

        return amnesty_modern_image_url($modern_formats[$relative_file][$mime_type]['file']);
    }
}

if (! function_exists('amnesty_get_modern_srcset')) {
    function amnesty_get_modern_srcset(int $attachment_id, string $mime_type, string $fallback_srcset): string
    {
        $fallback_srcset = trim($fallback_srcset);
        if ('' === $fallback_srcset) {
            return '';
        }

        $sources = array_map('trim', explode(',', $fallback_srcset));
        $modern_sources = [];

        foreach ($sources as $source) {
            if ('' === $source) {
                continue;
            }

            $parts = preg_split('/\s+/', $source, 2);
            $url = $parts[0] ?? '';
            $descriptor = $parts[1] ?? '';
            $modern_url = amnesty_get_modern_variant_for_url($attachment_id, $mime_type, $url);

            if ('' === $modern_url) {
                continue;
            }

            $modern_sources[] = trim($modern_url . ($descriptor ? ' ' . $descriptor : ''));
        }

        return implode(', ', $modern_sources);
    }
}

if (! function_exists('amnesty_img_tag_attribute')) {
    function amnesty_img_tag_attribute(string $img_tag, string $attribute): string
    {
        if (preg_match('/\s' . preg_quote($attribute, '/') . '\s*=\s*(["\'])(.*?)\1/i', $img_tag, $matches)) {
            return html_entity_decode($matches[2], ENT_QUOTES);
        }

        return '';
    }
}

if (! function_exists('amnesty_picture_from_attachment_html')) {
    function amnesty_picture_from_attachment_html(string $html, int $attachment_id): string
    {
        if ('' === trim($html) || str_contains($html, '<picture')) {
            return $html;
        }

        if (! preg_match('/<img\b[^>]*>/i', $html, $matches)) {
            return $html;
        }

        $img_tag = $matches[0];
        $src = amnesty_img_tag_attribute($img_tag, 'src');
        if ('' === $src) {
            return $html;
        }

        $fallback_srcset = amnesty_img_tag_attribute($img_tag, 'srcset') ?: $src;
        $sizes = amnesty_img_tag_attribute($img_tag, 'sizes');
        $source_sizes = $sizes ? ' sizes="' . esc_attr($sizes) . '"' : '';

        $sources = '';
        foreach (array_keys(amnesty_modern_image_formats()) as $mime_type) {
            $srcset = amnesty_get_modern_srcset($attachment_id, $mime_type, $fallback_srcset);
            if ('' === $srcset) {
                continue;
            }

            $sources .= sprintf(
                '<source type="%s" srcset="%s"%s />',
                esc_attr($mime_type),
                esc_attr($srcset),
                $source_sizes
            );
        }

        if ('' === $sources) {
            return $html;
        }

        $picture = '<picture>' . $sources . $img_tag . '</picture>';

        return str_replace($img_tag, $picture, $html);
    }
}

if (! function_exists('amnesty_get_attachment_picture')) {
    /**
     * @param array<string,string> $attr
     */
    function amnesty_get_attachment_picture(int $attachment_id, string|array $size = 'thumbnail', array $attr = []): string
    {
        if (! function_exists('wp_get_attachment_image')) {
            return '';
        }

        return wp_get_attachment_image($attachment_id, $size, false, $attr);
    }
}

if (! function_exists('amnesty_wrap_attachment_image_with_picture')) {
    /**
     * @param array<string,string> $attr
     */
    function amnesty_wrap_attachment_image_with_picture(string $html, int $attachment_id, string|array $size, bool $icon, array $attr): string
    {
        if (function_exists('is_admin') && is_admin()) {
            return $html;
        }

        if ($icon) {
            return $html;
        }

        return amnesty_picture_from_attachment_html($html, $attachment_id);
    }
}

if (function_exists('add_filter')) {
    add_filter('wp_generate_attachment_metadata', 'amnesty_generate_modern_image_variants_on_metadata_update', 20, 2);
    add_filter('wp_get_attachment_image', 'amnesty_wrap_attachment_image_with_picture', 20, 5);
}
