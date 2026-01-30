<?php

declare(strict_types=1);

function amnesty_handle_footer_newsletter_lead(): void
{
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['newsletter-lead'])) {
        return;
    }

    if (!isset($_POST['newsletter_lead_form_nonce']) ||
        !wp_verify_nonce($_POST['newsletter_lead_form_nonce'], 'newsletter_lead_form_action')) {
        wp_die('Échec de sécurité, veuillez réessayer.');
    }

    $email = sanitize_email($_POST['newsletter-lead'] ?? '');

    $local_user = get_local_user($email);
    $get_user_sf = get_salesforce_user_with_email($email);
    $contact_exist_on_salesforce = $get_user_sf['totalSize'] > 0;

    if ($local_user !== false) {
        $data = [
            'Email' => $local_user->email,
            'Salutation' => $local_user->civility,
            'Code_Postal__c' => $local_user->postal_code,
            'FirstName' => $local_user->firstname,
            'LastName' =>  $local_user->lastname,
            'Pays__c' => $local_user->country,
            'Optin_Actionaute_Newsletter_mensuelle__c' => true,
        ];

        if (!$contact_exist_on_salesforce) {
            post_salesforce_users([
                ...$data,
                'Origine__c' => getenv('AIF_SALESFORCE_ORIGINE__C'),
            ]);
        } else {
            update_salesforce_users($get_user_sf['records'][0]['Id'], [
                ...$data,
                'Optout_toute_communication__c' => false,
            ]);
        }

        wp_safe_redirect(add_query_arg([
            'inscription__nl__footer' => 'success',
            'gtm_type' => 'inscription',
            'gtm_name' => 'newsletter',
        ], home_url()));
        exit;
    }

    if ($contact_exist_on_salesforce) {
        $sf_user = $get_user_sf['records'][0];
        if ($local_user === false) {
            insert_user(
                $sf_user['Civility__c'] ?? null,
                $sf_user['FirstName'],
                $sf_user['LastName'],
                $sf_user['Email'],
                $sf_user['Pays__c'] ?? null,
                $sf_user['Code_Postal__c'] ?? null,
                $sf_user['MobilePhone'] ?? null
            );
        }
        $data = [
            ...$sf_user,
            'Optin_Actionaute_Newsletter_mensuelle__c' => true,
        ];

        $user_id = $data['Id'];
        unset($data['Id']);
        unset($data['attributes']);

        update_salesforce_users($user_id, $data);

        wp_safe_redirect(add_query_arg([
            'inscription__nl__footer' => 'success',
            'gtm_type' => 'inscription',
            'gtm_name' => 'newsletter',
        ], home_url()));
        exit;
    }

    $get_current_sf_lead = get_salesforce_nl_lead($email);
    $lead_exist_on_sf = $get_current_sf_lead['totalSize'] > 0;

    $new_lead = [
        'Email' => $email,
        'LastName' => '_aucun_',
        'Code_Origine__c' => getenv('AIF_SALESFORCE_CODE_ORIGINE__C__WEB'),
        'Optin_Actionaute_Newsletter_mensuelle__c' => true,
    ];

    if (false === $lead_exist_on_sf) {
        register_salesforce_lead($new_lead);
        wp_safe_redirect(add_query_arg('email', urlencode($email), home_url('/newsletter')));
        exit;
    }

    if (!$contact_exist_on_salesforce) {
        update_salesforce_lead($get_current_sf_lead['records'][0]['Id'], $new_lead);
        wp_safe_redirect(add_query_arg('email', urlencode($email), home_url('/newsletter')));
        exit;
    }

    wp_safe_redirect(home_url('/newsletter'));
    exit;
}

add_action('template_redirect', 'amnesty_handle_footer_newsletter_lead');
