<?php

add_action('rest_api_init', function () {
    register_rest_route('aif-donor-space/v1', '/duplicate-tax-receipt-request/', [
        'methods' => 'POST',
        'callback' => 'handle_duplicate_tax_receipt_request',
        'permission_callback' => 'aif_check_nonce',
    ]);
});

function handle_duplicate_tax_receipt_request(WP_REST_Request $request)
{

    $params = $request->get_json_params();

    $userID = get_current_user_id();
    $SF_ID = get_SF_user_ID($userID);
    $taxt_receipt_reference = $params['taxReceiptReference'] ?? null;

    if (!$SF_ID || !is_string($taxt_receipt_reference) || '' === trim($taxt_receipt_reference)) {
        return new WP_REST_Response(['message' => 'tax receipt ID not provided'], status: 403);
    }

    $owns_receipt = aif_contact_owns_tax_receipt($SF_ID, $taxt_receipt_reference);

    if (is_wp_error($owns_receipt)) {
        return aif_salesforce_unavailable_response($owns_receipt);
    }

    // Same response whether the receipt belongs to another donor or does not exist.
    if (!$owns_receipt) {
        return new WP_REST_Response(['message' => 'tax receipt not found'], status: 403);
    }

    $result = create_duplicate_taxt_receipt_request($SF_ID, $taxt_receipt_reference);

    if (is_wp_error($result)) {
        return aif_salesforce_unavailable_response($result);
    }

    if (!is_object($result) || empty($result->success)) {
        return new WP_REST_Response(['message' => 'demand failed'], status: 400);
    }

    $response = [
        'message' => 'demand succeed',
        'result' => $result,
    ];

    return new WP_REST_Response($response, 200);
}

function aif_salesforce_unavailable_response(WP_Error $error)
{
    aif_log_salesforce_error($error);

    return new WP_REST_Response(['message' => 'service temporarily unavailable'], status: 503);
}

function aif_check_nonce(WP_REST_Request $request)
{

    $nonce = $request->get_header('X-WP-Nonce');

    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Invalid nonce.', ['status' => 403]);
    }

    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'Not logged in.', ['status' => 403]);

    }

    if (!get_email_is_verified(get_current_user_id())) {
        return new WP_Error('rest_forbidden', 'Email not verified.', ['status' => 403]);
    }


    return true;
}
