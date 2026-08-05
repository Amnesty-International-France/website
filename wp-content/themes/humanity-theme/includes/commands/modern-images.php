<?php

declare(strict_types=1);

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

if (! class_exists('Amnesty_Modern_Images_Command')) {
    class Amnesty_Modern_Images_Command
    {
        /**
         * Generate missing AVIF/WebP variants for existing media.
         *
         * ## OPTIONS
         *
         * [--missing-only]
         * : Keep existing modern variants.
         *
         * [--force]
         * : Regenerate variants even when metadata already references one.
         *
         * [--dry-run]
         * : Report what would be processed without writing metadata.
         *
         * [--batch-size=<number>]
         * : Number of attachments per page. Default: 50.
         *
         * [--ids=<ids>]
         * : Comma-separated attachment IDs to process.
         *
         * @param array<int,string> $args
         * @param array<string,mixed> $assoc_args
         */
        public function modernize(array $args, array $assoc_args): void
        {
            $force = ! empty($assoc_args['force']);
            $dry_run = ! empty($assoc_args['dry-run']);
            $batch_size = max(1, (int) ($assoc_args['batch-size'] ?? 50));
            $ids = $this->get_attachment_ids($assoc_args, $batch_size);

            $processed = 0;
            $updated = 0;
            $skipped = 0;
            $saved_bytes = 0;

            foreach ($ids as $attachment_id) {
                $processed++;
                $metadata = wp_get_attachment_metadata($attachment_id);

                if (! is_array($metadata)) {
                    $skipped++;
                    WP_CLI::log(sprintf('%d skipped: no image metadata', $attachment_id));
                    continue;
                }

                $before = $metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY] ?? [];
                $next_metadata = $dry_run ? $metadata : amnesty_generate_modern_image_variants($attachment_id, $metadata, $force);
                $after = $next_metadata[AMNESTY_MODERN_IMAGE_METADATA_KEY] ?? [];

                if ($dry_run) {
                    WP_CLI::log(sprintf('%d would be processed', $attachment_id));
                    continue;
                }

                if ($before === $after) {
                    $skipped++;
                    continue;
                }

                wp_update_attachment_metadata($attachment_id, $next_metadata);
                $updated++;
                $saved_bytes += $this->saved_bytes($metadata, $next_metadata);
                WP_CLI::log(sprintf('%d modern variants updated', $attachment_id));
            }

            WP_CLI::success(
                sprintf(
                    'Processed %d attachment(s), updated %d, skipped %d, generated about %s of transfer savings.',
                    $processed,
                    $updated,
                    $skipped,
                    size_format($saved_bytes)
                )
            );
        }

        /**
         * @param array<string,mixed> $assoc_args
         * @return array<int,int>
         */
        private function get_attachment_ids(array $assoc_args, int $batch_size): array
        {
            if (! empty($assoc_args['ids']) && is_string($assoc_args['ids'])) {
                return array_values(
                    array_filter(
                        array_map('absint', explode(',', $assoc_args['ids'])),
                        static fn (int $id): bool => $id > 0
                    )
                );
            }

            $ids = [];
            $offset = 0;

            do {
                $batch = get_posts([
                    'post_type'      => 'attachment',
                    'post_mime_type' => [ 'image/jpeg', 'image/png' ],
                    'post_status'    => 'inherit',
                    'posts_per_page' => $batch_size,
                    'offset'         => $offset,
                    'fields'         => 'ids',
                    'orderby'        => 'ID',
                    'order'          => 'ASC',
                ]);

                $ids = array_merge($ids, array_map('absint', $batch));
                $offset += $batch_size;
            } while (count($batch) === $batch_size);

            return $ids;
        }

        /**
         * @param array<string,mixed> $before
         * @param array<string,mixed> $after
         */
        private function saved_bytes(array $before, array $after): int
        {
            $saved = 0;
            $formats = $after[AMNESTY_MODERN_IMAGE_METADATA_KEY] ?? [];

            if (! is_array($formats)) {
                return 0;
            }

            foreach ($formats as $relative_file => $variants) {
                if (! is_array($variants)) {
                    continue;
                }

                $source_size = $this->source_filesize($relative_file, $before);
                if ($source_size < 1) {
                    continue;
                }

                foreach ($variants as $variant) {
                    if (! is_array($variant) || empty($variant['filesize'])) {
                        continue;
                    }

                    $saved += max(0, $source_size - (int) $variant['filesize']);
                }
            }

            return $saved;
        }

        /**
         * @param array<string,mixed> $metadata
         */
        private function source_filesize(string $relative_file, array $metadata): int
        {
            if (($metadata['file'] ?? '') === $relative_file && ! empty($metadata['filesize'])) {
                return (int) $metadata['filesize'];
            }

            $dirname = amnesty_modern_image_relative_dir($metadata);
            foreach (($metadata['sizes'] ?? []) as $size) {
                if (! is_array($size) || empty($size['file'])) {
                    continue;
                }

                $size_relative = ltrim(($dirname ? $dirname . '/' : '') . $size['file'], '/');
                if ($size_relative === $relative_file && ! empty($size['filesize'])) {
                    return (int) $size['filesize'];
                }
            }

            return 0;
        }
    }
}

WP_CLI::add_command('amnesty media', Amnesty_Modern_Images_Command::class);
