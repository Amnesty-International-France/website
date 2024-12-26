<?php

/* Template Name: Espace Donateur - Home */
get_header();

if (is_user_logged_in()) {

    $current_user = wp_get_current_user();
    $sf_user = get_salesforce_member_data($current_user->user_email);

    if (has_access_to_donation_space($sf_user)) {
        aif_donor_space_get_partial("tax-reciept") ;
    } else {
        wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
    }


} else {

    wp_redirect(get_permalink(get_page_by_path('connectez-vous')));
    exit;
}

get_footer();
