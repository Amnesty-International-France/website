<?php

function email_rest_endpoint(WP_REST_Request $request)
{
    global $wpdb;

    $email = sanitize_email($request->get_param('email'));

    if (! is_email($email)) {
        return new WP_Error('invalid_email', 'Email not valid.', [ 'status' => 400 ]);
    }

    $table_name = $wpdb->prefix . 'aif_users';
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email);
    $count = $wpdb->get_var($sql);

    return new WP_REST_Response(['exists' => ($count > 0)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('humanity/v1', '/check-email', [
        'methods' => 'POST',
        'callback' => 'email_rest_endpoint',
        'permission_callback' => '__return_true',
    ]);
});
