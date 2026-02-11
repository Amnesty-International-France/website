<?php

$id = get_the_ID();
$attachment = function_exists('get_field') ? get_field('upload_du_document', $id) : null;
$attachment_id = is_array($attachment) ? ($attachment['ID'] ?? 0) : 0;

if (! $attachment_id) {
    status_header(404);
    nocache_headers();
    exit;
}

$attachment_url = wp_get_attachment_url($attachment_id);
if (! $attachment_url) {
    status_header(404);
    nocache_headers();
    exit;
}

$is_private = function_exists('amnesty_document_is_private') && amnesty_document_is_private($id);

if ($is_private) {
    // Serve private documents directly to attach X-Robots-Tag headers.
    header('X-Robots-Tag: noindex, nofollow', true);

    $file_path = get_attached_file($attachment_id);
    if ($file_path && is_readable($file_path)) {
        $mime_type = get_post_mime_type($attachment_id);
        if (! $mime_type) {
            $filetype = wp_check_filetype($file_path);
            $mime_type = $filetype['type'] ?: 'application/octet-stream';
        }

        nocache_headers();
        header('Content-Type: ' . $mime_type);

        $file_size = filesize($file_path);
        if (false !== $file_size) {
            header('Content-Length: ' . $file_size);
        }

        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('X-Content-Type-Options: nosniff');

        readfile($file_path);
        exit;
    }
}

wp_redirect($attachment_url);
exit;
