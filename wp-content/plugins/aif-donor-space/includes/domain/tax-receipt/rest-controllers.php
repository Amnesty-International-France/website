<?php

add_action('rest_api_init', function () {
    register_rest_route('aif-donor-space/v1', '/duplicate-tax-receipt-request/', array(
        'methods' => 'POST',
        'callback' => 'handle_duplicate_tax_receipt_request',
        'permission_callback' => 'check_nonce',
    ));
});

function handle_duplicate_tax_receipt_request(WP_REST_Request $request)
{

    $params = $request->get_json_params();

    $userID = get_current_user_id();
    $SF_ID = get_SF_user_ID($userID);
    $taxt_receipt_reference = $params['taxReceiptReference'];

    if (!$SF_ID || !$taxt_receipt_reference) {
        return new WP_REST_Response(array('status' => 403,'message' => 'tax receipt ID not provided'));
    }



    $result = create_duplicate_taxt_receipt_request($SF_ID, $taxt_receipt_reference);

    // if (!$result['success']) {
    //     return new WP_REST_Response(['message' => 'demand failed'], status: 400);
    // }

    $response = array(
        'message' => 'Tax receipt duplicated successfully!',
        'result' => $result
    );
    return new WP_REST_Response($response, 200);
}

function check_nonce(WP_REST_Request $request)
{

    $nonce = $request->get_header('X-WP-Nonce');

    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Invalid nonce.', array('status' => 403));
    }

    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'Not logged in.', array('status' => 403));

    }


    return true;
}
