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
    $taxt_receipt_reference = $params['taxReceiptReference'] ?? null;

    if (!is_string($taxt_receipt_reference) || '' === trim($taxt_receipt_reference)) {
        return new WP_REST_Response(['message' => 'tax receipt ID not provided'], status: 403);
    }

    $user_id = get_current_user_id();
    $request_key = aif_duplicate_tax_receipt_request_key($user_id, $taxt_receipt_reference);
    $count_key = aif_duplicate_tax_receipt_requests_count_key($user_id);

    if (false !== get_transient($request_key)) {
        return new WP_REST_Response(['message' => 'demand already sent'], 200);
    }

    $requests_count = (int) get_transient($count_key);

    if ($requests_count >= AIF_DUPLICATE_TAX_RECEIPT_DAILY_LIMIT) {
        return new WP_REST_Response(['message' => 'too many demands'], status: 429);
    }

    set_transient($request_key, time(), DAY_IN_SECONDS);
    set_transient($count_key, $requests_count + 1, DAY_IN_SECONDS);

    $response = aif_send_duplicate_tax_receipt_request($taxt_receipt_reference);

    if (200 !== $response->get_status()) {
        aif_release_duplicate_tax_receipt_request($request_key, $count_key);
    }

    return $response;
}

function aif_send_duplicate_tax_receipt_request($taxt_receipt_reference)
{
    $sf_member = aif_validate_salesforce_member(get_salesforce_member_data(wp_get_current_user()->user_email));
    if (is_wp_error($sf_member)) {
        return aif_salesforce_unavailable_response($sf_member);
    }

    if (aif_is_salesforce_contact_absent($sf_member) || !has_access_to_donation_space($sf_member)) {
        return new WP_REST_Response(['message' => 'donation space access denied'], status: 403);
    }

    $SF_ID = $sf_member->Id;

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

    return new WP_REST_Response(['message' => 'demand succeed'], 200);
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
