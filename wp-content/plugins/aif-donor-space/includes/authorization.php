<?php

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
    $sf_user = get_salesforce_member_data($current_user->user_email);


    if (!$sf_user) {
        wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
        exit;
    }


    if (!has_access_to_donation_space($sf_user)) {
        wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
        exit;
    }

}
