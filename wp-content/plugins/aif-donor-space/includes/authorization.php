<?php

require_once __DIR__ . '/sales-force/errors.php';

function aif_salesforce_service_unavailable($error)
{
    aif_log_salesforce_error($error);
    wp_die(
        AIF_SALESFORCE_SERVICE_UNAVAILABLE_MESSAGE,
        'Service temporairement indisponible',
        ['response' => 503]
    );
}

function aif_require_salesforce_object($result, $endpoint, $required_property = null)
{
    if (is_wp_error($result)) {
        aif_salesforce_service_unavailable($result);
    }

    if (!is_object($result) || (null !== $required_property && !property_exists($result, $required_property))) {
        aif_salesforce_service_unavailable(
            aif_create_salesforce_invalid_response_error('GET', $endpoint)
        );
    }

    return $result;
}

function aif_require_salesforce_records($result, $endpoint)
{
    $result = aif_require_salesforce_object($result, $endpoint, 'records');

    if (!is_array($result->records)) {
        aif_salesforce_service_unavailable(
            aif_create_salesforce_invalid_response_error('GET', $endpoint)
        );
    }

    return $result;
}

function aif_require_salesforce_array($result, $endpoint)
{
    if (is_wp_error($result)) {
        aif_salesforce_service_unavailable($result);
    }

    if (!is_array($result)) {
        aif_salesforce_service_unavailable(
            aif_create_salesforce_invalid_response_error('GET', $endpoint)
        );
    }

    return $result;
}

function aif_validate_salesforce_member($sf_member)
{
    if (is_wp_error($sf_member)) {
        return $sf_member;
    }

    if (!is_object($sf_member)) {
        return aif_create_salesforce_invalid_response_error('GET', 'member_search');
    }

    if (aif_is_salesforce_contact_absent($sf_member)) {
        return $sf_member;
    }

    $required_properties = ['Id', 'isDonateur', 'isMembre', 'hasMandatActif'];

    foreach ($required_properties as $property) {
        if (!property_exists($sf_member, $property)) {
            return aif_create_salesforce_invalid_response_error('GET', 'member_search');
        }
    }

    if (empty($sf_member->Id)) {
        return aif_create_salesforce_invalid_response_error('GET', 'member_search');
    }

    return $sf_member;
}

function aif_is_salesforce_contact_absent($sf_member)
{
    return is_object($sf_member) && [] === get_object_vars($sf_member);
}

function check_user_page_access()
{
    global $wp;
    $current_url = home_url(add_query_arg([], $wp->request));
    $login_page_url = get_permalink(get_page_by_path('connectez-vous'));
    $redirect_url = add_query_arg('redirect_to', urlencode($current_url), $login_page_url);

    if (!is_user_logged_in()) {
        wp_redirect($redirect_url);
        exit;
    }

    $current_user = wp_get_current_user();
    $sf_user = aif_validate_salesforce_member(get_salesforce_member_data($current_user->user_email));

    if (is_wp_error($sf_user)) {
        aif_salesforce_service_unavailable($sf_user);
    }

    if (aif_is_salesforce_contact_absent($sf_user) || !has_access_to_donation_space($sf_user)) {
        wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
        exit;
    }

    return $sf_user;
}

function aif_get_request_salesforce_member()
{
    $sf_member = get_query_var('aif_salesforce_member', null);

    if (is_wp_error($sf_member)) {
        aif_salesforce_service_unavailable($sf_member);
    }

    if (!is_object($sf_member)) {
        aif_salesforce_service_unavailable(
            aif_create_salesforce_invalid_response_error('GET', 'member_search')
        );
    }

    return $sf_member;
}
