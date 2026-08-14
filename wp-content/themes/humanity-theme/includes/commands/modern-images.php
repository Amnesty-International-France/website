<?php

declare(strict_types=1);

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

if (! class_exists('Amnesty_Modern_Images_Command')) {
    class Amnesty_Modern_Images_Command
    {
        /**
         * Queue missing AVIF/WebP variants for existing media.
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
            $queued = 0;
            $skipped = 0;

            foreach ($ids as $attachment_id) {
                $processed++;
                $metadata = wp_get_attachment_metadata($attachment_id);

                if (! is_array($metadata)) {
                    $skipped++;
                    WP_CLI::log(sprintf('%d skipped: no image metadata', $attachment_id));
                    continue;
                }

                $attached_file = get_attached_file($attachment_id);
                if (! is_string($attached_file) || '' === $attached_file) {
                    $skipped++;
                    WP_CLI::log(sprintf('%d skipped: no attached file', $attachment_id));
                    continue;
                }

                $tasks = amnesty_modern_image_tasks($metadata, $attached_file, $force);

                if ($dry_run) {
                    WP_CLI::log(sprintf('%d would queue %d modern variant task(s)', $attachment_id, count($tasks)));
                    continue;
                }

                if ($tasks === []) {
                    $skipped++;
                    continue;
                }

                $queued += amnesty_enqueue_modern_image_variants($attachment_id, $metadata, $force);
                WP_CLI::log(sprintf('%d modern variant task(s) queued for attachment %d', count($tasks), $attachment_id));
            }

            WP_CLI::success(
                sprintf(
                    'Processed %d attachment(s), queued %d task(s), skipped %d.',
                    $processed,
                    $queued,
                    $skipped
                )
            );
        }

        /**
         * Process queued AVIF/WebP variant tasks.
         *
         * ## OPTIONS
         *
         * [--limit=<number>]
         * : Maximum number of variant tasks to process. Default: 20.
         *
         * [--time-limit=<seconds>]
         * : Maximum runtime budget. Default: 45.
         *
         * [--max-attempts=<number>]
         * : Number of conversion attempts before a task is marked failed. Default: 3.
         *
         * @param array<int,string> $args
         * @param array<string,mixed> $assoc_args
         */
        public function process_modern_queue(array $args, array $assoc_args): void
        {
            $stats = amnesty_process_modern_image_queue(
                max(1, (int) ($assoc_args['limit'] ?? 20)),
                max(1, (int) ($assoc_args['time-limit'] ?? 45)),
                max(1, (int) ($assoc_args['max-attempts'] ?? 3))
            );

            WP_CLI::success(
                sprintf(
                    'Processed %d task(s), updated %d, failed %d, skipped %d, locked %d.',
                    $stats['processed'],
                    $stats['updated'],
                    $stats['failed'],
                    $stats['skipped'],
                    $stats['locked']
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

    }
}

WP_CLI::add_command('amnesty media', Amnesty_Modern_Images_Command::class);
