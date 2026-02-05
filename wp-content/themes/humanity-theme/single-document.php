<?php

$id = (int) get_the_ID();

if (! $id) {
    wp_safe_redirect(home_url('/documents/'));
    exit();
}

if (amnesty_is_private_document($id)) {
    amnesty_sync_document_private_storage($id);

    if (! is_user_logged_in()) {
        wp_safe_redirect(amnesty_get_document_login_url());
        exit();
    }

    if (amnesty_stream_private_document($id)) {
        exit();
    }

    wp_safe_redirect(home_url('/documents/'));
    exit();
}

$attachment_url = amnesty_get_document_attachment_url($id);
if ($attachment_url === '') {
    wp_safe_redirect(home_url('/documents/'));
    exit();
}

wp_safe_redirect($attachment_url);
exit();
