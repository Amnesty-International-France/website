<?php

$id = get_the_ID();
$attachment_id = function_exists('amnesty_document_get_attachment_id')
    ? amnesty_document_get_attachment_id($id)
    : 0;

$attachment_url = wp_get_attachment_url($attachment_id);
if (! $attachment_url) {
    status_header(404);
    nocache_headers();
    exit;
}

$is_private = function_exists('amnesty_document_is_private') && amnesty_document_is_private($id);

if ($is_private) {
    header('X-Robots-Tag: noindex, nofollow', true);

    if (! is_user_logged_in()) {
        $login_page = get_page_by_path('connectez-vous');
        $login_url = $login_page ? get_permalink($login_page) : home_url('/connectez-vous/');

        wp_redirect($login_url);
        exit;
    }

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

    status_header(404);
    nocache_headers();
    exit;
}

wp_redirect($attachment_url);
exit;
