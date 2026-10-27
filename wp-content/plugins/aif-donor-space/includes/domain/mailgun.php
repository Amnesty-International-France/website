<?php

function is_mailgun_send_successful($response, $template)
{
    if (is_wp_error($response)) {
        error_log(sprintf('Mailgun failed to send mail with template "%s": %s', $template, $response->get_error_code()));
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code < 200 || $status_code >= 300) {
        error_log(sprintf('Mailgun failed to send mail with template "%s" and returned HTTP code %d', $template, $status_code));
        return false;
    }

    return true;
}
