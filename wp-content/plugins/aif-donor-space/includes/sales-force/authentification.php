<?php

require_once __DIR__ . '/errors.php';

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
    $started_at = microtime(true);
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
        return aif_create_salesforce_transport_error($response, 'OAUTH', $url, $started_at);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $response_data = [
        'status' => $status_code,
        'body_size' => strlen($body),
    ];

    if ($status_code < 200 || $status_code >= 300) {
        $error_code = in_array($status_code, [400, 401, 403], true)
            ? 'salesforce_authentication_error'
            : 'salesforce_http_error';

        return new WP_Error(
            $error_code,
            'L’authentification Salesforce a échoué.',
            aif_get_salesforce_error_data('OAUTH', $url, $started_at, $response_data)
        );
    }

    if ('' === trim($body)) {
        return new WP_Error(
            'salesforce_empty_response',
            'Salesforce a retourné une réponse vide.',
            aif_get_salesforce_error_data('OAUTH', $url, $started_at, $response_data)
        );
    }

    try {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return new WP_Error(
            'salesforce_invalid_json',
            'Salesforce a retourné une réponse JSON invalide.',
            aif_get_salesforce_error_data('OAUTH', $url, $started_at, array_merge($response_data, [
                'json_error' => $exception->getMessage(),
            ]))
        );
    }

    if (null === $data) {
        return new WP_Error(
            'salesforce_null_response',
            'Salesforce a retourné une réponse nulle.',
            aif_get_salesforce_error_data('OAUTH', $url, $started_at, $response_data)
        );
    }

    if (!is_array($data) || empty($data['access_token']) || empty($data['issued_at'])) {
        return new WP_Error(
            'salesforce_invalid_response',
            'La réponse d’authentification Salesforce est invalide.',
            aif_get_salesforce_error_data('OAUTH', $url, $started_at, $response_data)
        );
    }

    $issued_at = intval($data['issued_at']); // warning : this is ms and not seconds
    $expiration_interval = 10 * 60 * 1000; // 10 minutes in milliseconds
    $expiration_time = $issued_at + $expiration_interval ;

    update_option('salesforce_access_token', $data['access_token']);
    update_option('salesforce_token_expiration_time', $expiration_time);

    return $data['access_token'];
}
