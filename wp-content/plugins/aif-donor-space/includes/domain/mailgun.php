<?php

function aif_is_mailgun_send_successful($response, $template)
{
    if (is_wp_error($response)) {
        error_log(sprintf('Mailgun failed to send mail with template "%s": %s (%s)', $template, $response->get_error_code(), $response->get_error_message()));
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);

    if ($status_code !== 200) {
        error_log(sprintf('Mailgun failed to send mail with template "%s" and returned HTTP code %d: %s', $template, $status_code, wp_remote_retrieve_body($response)));
        return false;
    }

    return true;
}
