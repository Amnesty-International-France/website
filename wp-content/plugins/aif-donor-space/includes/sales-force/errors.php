<?php

const AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE = 'Votre espace est temporairement indisponible. Merci de réessayer dans quelques instants.';

function aif_get_salesforce_endpoint_name($url)
{
    $routes = [
        'services/oauth2/token' => 'oauth_token',
        'services/apexrest/search/v1/' => 'member_search',
        'RecuFiscaux' => 'tax_receipts',
        'Demandes' => 'demands',
        'Mandats_SEPA__r' => 'sepa_mandates',
        'sobjects/Contact/' => 'contact',
        'sobjects/Case' => 'case',
    ];

    foreach ($routes as $route => $endpoint) {
        if (false !== strpos((string) $url, $route)) {
            return $endpoint;
        }
    }

    return 'salesforce_api';
}

function aif_get_salesforce_error_data($operation, $url, $started_at, $data = [])
{
    return array_merge([
        'operation' => strtoupper((string) $operation),
        'endpoint' => aif_get_salesforce_endpoint_name($url),
        'duration_ms' => max(0, (int) round((microtime(true) - $started_at) * 1000)),
    ], $data);
}

function aif_create_salesforce_transport_error($error, $operation, $url, $started_at)
{
    return new WP_Error(
        'salesforce_transport_error',
        'La requête Salesforce a échoué.',
        aif_get_salesforce_error_data($operation, $url, $started_at, [
            'cause_code' => $error->get_error_code(),
            'cause_class' => get_class($error),
        ])
    );
}

function aif_create_salesforce_invalid_response_error($operation, $endpoint)
{
    return new WP_Error(
        'salesforce_invalid_response',
        'La réponse Salesforce est invalide.',
        [
            'operation' => strtoupper((string) $operation),
            'endpoint' => (string) $endpoint,
        ]
    );
}

function aif_create_salesforce_missing_identifier_error($operation, $endpoint)
{
    return new WP_Error(
        'salesforce_missing_identifier',
        'L’identifiant Salesforce requis est absent.',
        [
            'operation' => strtoupper((string) $operation),
            'endpoint' => (string) $endpoint,
        ]
    );
}

function aif_get_salesforce_log_context($error)
{
    $error_data = $error->get_error_data();
    $error_data = is_array($error_data) ? $error_data : [];
    $context = [
        'error_code' => $error->get_error_code(),
        'error_class' => get_class($error),
    ];
    $allowed_fields = [
        'operation' => 'operation',
        'endpoint' => 'endpoint',
        'duration_ms' => 'duration_ms',
        'cause_code' => 'cause_code',
        'cause_class' => 'cause_class',
        'status' => 'http_status',
        'body_size' => 'body_size',
        'json_error' => 'json_error',
    ];

    foreach ($allowed_fields as $source => $destination) {
        if (array_key_exists($source, $error_data)) {
            $context[$destination] = $error_data[$source];
        }
    }

    return $context;
}

function aif_log_salesforce_error($error)
{
    if (!is_wp_error($error)) {
        return;
    }

    $context = aif_get_salesforce_log_context($error);
    $message = sprintf('Salesforce request failed: %s', $error->get_error_code());
    $sentry_enabled = defined('WP_SENTRY_PHP_DSN')
        && !empty(WP_SENTRY_PHP_DSN)
        && function_exists('Sentry\\withScope')
        && function_exists('Sentry\\captureMessage')
        && class_exists('Sentry\\Severity');

    if ($sentry_enabled) {
        call_user_func('Sentry\\withScope', function ($scope) use ($context, $message) {
            $scope->setTag('salesforce.error_code', $context['error_code']);

            if (isset($context['endpoint'])) {
                $scope->setTag('salesforce.endpoint', $context['endpoint']);
            }

            $scope->setContext('salesforce', $context);
            $severity_class = 'Sentry\\Severity';
            call_user_func('Sentry\\captureMessage', $message, $severity_class::error());
        });

        return;
    }

    $encoded_context = function_exists('wp_json_encode')
        ? wp_json_encode($context)
        : json_encode($context);

    error_log(sprintf('%s %s', $message, $encoded_context ?: '{}'));
}

function aif_parse_salesforce_response($response, $operation, $url, $started_at, $allow_no_content = false)
{
    if (is_wp_error($response)) {
        return aif_create_salesforce_transport_error($response, $operation, $url, $started_at);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $response_data = [
        'status' => $status_code,
        'body_size' => strlen($body),
    ];

    if ($status_code < 200 || $status_code >= 300) {
        return new WP_Error(
            'salesforce_http_error',
            'La requête Salesforce a échoué.',
            aif_get_salesforce_error_data($operation, $url, $started_at, $response_data)
        );
    }

    if ($allow_no_content && 204 === $status_code && '' === trim($body)) {
        return true;
    }

    if ('' === trim($body)) {
        return new WP_Error(
            'salesforce_empty_response',
            'Salesforce a retourné une réponse vide.',
            aif_get_salesforce_error_data($operation, $url, $started_at, $response_data)
        );
    }

    try {
        $decoded_data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return new WP_Error(
            'salesforce_invalid_json',
            'Salesforce a retourné une réponse JSON invalide.',
            aif_get_salesforce_error_data($operation, $url, $started_at, array_merge($response_data, [
                'json_error' => $exception->getMessage(),
            ]))
        );
    }

    return null === $decoded_data
        ? new WP_Error(
            'salesforce_null_response',
            'Salesforce a retourné une réponse nulle.',
            aif_get_salesforce_error_data($operation, $url, $started_at, $response_data)
        )
        : $decoded_data;
}
