<?php

function get_salesforce_access_token_donor_space_donor_space()
{
    $access_token = get_option('salesforce_access_token');
    $expiration_time = intval(get_option('salesforce_token_expiration_time') ?? -1);

    $current_time_in_ms  = floor(microtime(true) * 1000);

    $is_valid = $expiration_time > $current_time_in_ms;

    if ($is_valid) {
        return $access_token;
    }

    return refresh_salesforce_token_donor_space();
}


function refresh_salesforce_token_donor_space()
{
    $aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
    $client_id = defined('AIF_SALESFORCE_CLIENT_ID') ? AIF_SALESFORCE_CLIENT_ID : getenv('AIF_SALESFORCE_CLIENT_ID');
    $client_secret = defined('AIF_SALESFORCE_SECRET') ? AIF_SALESFORCE_SECRET : getenv('AIF_SALESFORCE_SECRET');
    $url = $aif_salesforce_base_url . 'services/oauth2/token';

    $params = [
        'grant_type'    => 'client_credentials',
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
    ];

    $response = wp_remote_post($url, [
        'method'    => 'POST',
        'body'      => $params,
        'timeout'   => 15,
        'headers'   => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('request_failed', 'La requête a échoué', $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);

    $data = json_decode($body, true);

    if (isset($data['access_token'])) {

        $issued_at = intval($data['issued_at']); // warning : this is ms and not seconds
        $expiration_interval = 10 * 60 * 1000; // 10 minutes in milliseconds
        $expiration_time = $issued_at + $expiration_interval ;

        update_option('salesforce_access_token', $data['access_token']);
        update_option('salesforce_token_expiration_time', $expiration_time);

        return $data['access_token'];
    } else {
        return new WP_Error('token_refresh_failed', 'Le rafraîchissement du token a échoué', $data);
    }
}
