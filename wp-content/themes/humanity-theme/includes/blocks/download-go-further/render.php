<?php

declare(strict_types=1);

if (!function_exists('render_download_go_further_block')) {
    /**
     * Render the "Download Go Further" block
     *
     * @param array<string, mixed> $attributes Block attributes
     *
     * @return string HTML output
     */
    function render_download_go_further_block(array $attributes): string {
        $title   = $attributes['title'] ?? '';
        $fileIds = $attributes['fileIds'] ?? [];

        if (!function_exists('get_human_readable_file_type')) {
            function get_human_readable_file_type($mimeType) {
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/pjpeg':
                        return 'JPEG';
                    case 'image/png':
                        return 'PNG';
                    case 'image/gif':
                        return 'GIF';
                    case 'image/bmp':
                    case 'image/x-ms-bmp':
                        return 'BMP';
                    case 'image/webp':
                        return 'WebP';
                    case 'application/pdf':
                        return 'PDF';
                    case 'application/msword':
                    case 'application/vnd.ms-word':
                        return 'Document Word (DOC)';
                    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                        return 'Document Word (DOCX)';
                    case 'application/vnd.ms-excel':
                    case 'application/vnd.openxmlformats.officedocument.spreadsheetml.sheet':
                        return 'Feuille de calcul Excel';
                    case 'application/vnd.oasis.opendocument.spreadsheet':
                        return 'Feuille de calcul OpenDocument';
                    case 'application/vnd.oasis.opendocument.text':
                        return 'Document OpenDocument Text';
                    case 'audio/mpeg':
                    case 'audio/mp3':
                        return 'Fichier audio MP3';
                    case 'audio/ogg':
                        return 'Fichier audio OGG';
                    case 'audio/wav':
                        return 'Fichier audio WAV';
                    case 'video/mp4':
                        return 'Vidéo MP4';
                    case 'video/mpeg':
                        return 'Vidéo MPEG';
                    case 'video/quicktime':
                        return 'Vidéo QuickTime';
                    case 'text/plain':
                        return 'Fichier texte';
                    case 'application/zip':
                        return 'Archive ZIP';
                    default:
                        $parts = explode('/', $mimeType);
                        if (isset($parts[1])) {
                            return ucfirst($parts[1]) . ' File';
                        } else {
                            return 'Fichier';
                        }
                }
            }
        }

        ob_start();
        ?>
        <div class="download-go-further-block">
            <?php if ($title): ?>
                <div class="title-container">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                        />
                    </svg>
                    <h3 class="title"><?php echo esc_html($title); ?></h3>
                </div>
            <?php endif; ?>

            <?php if (!empty($fileIds)): ?>
                <ul class="list">
                    <?php foreach ($fileIds as $fileId): ?>
                        <?php
                            $url      = wp_get_attachment_url($fileId);
                            $title    = get_the_title($fileId);
                            $meta     = wp_get_attachment_metadata($fileId);
                            $size     = isset($meta['filesize']) ? round($meta['filesize'] / 1024, 2) . ' kb' : '—';
                            $mimeType = get_post_mime_type($fileId);
                            $type     = get_human_readable_file_type($mimeType);
                        ?>
                        <?php if ($url && $title): ?>
                            <li class="item">
                                <p class="item-text">
                                    <?php echo esc_html(sprintf('%s (%s, %s)', $title, $type, $size)); ?>
                                </p>
                                <a href="<?php echo esc_url($url); ?>" download>
                                    <button class="item-button">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            >
                                            <path
                                                fill-rule="evenodd"
                                                d="M19.5 21a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-5.379a.75.75 0 0 1-.53-.22L11.47 3.66A2.25 2.25 0 0 0 9.879 3H4.5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h15Zm-6.75-10.5a.75.75 0 0 0-1.5 0v4.19l-1.72-1.72a.75.75 0 0 0-1.06 1.06l3 3a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72V10.5Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <?php esc_html_e('Télécharger', 'amnesty'); ?>
                                    </button>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }
}
