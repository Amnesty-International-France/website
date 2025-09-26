<?php

declare(strict_types=1);

const URL = 'services/data/v57.0/';
const QUERY_LEAD = 'query/?q=SELECT+Id,Email+FROM+Lead';

function register_salesforce_newsletter(array $data)
{
    return post_salesforce_users($data);
}

function get_salesforce_nl_lead(string $email)
{
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Email not valid.', ['status' => 400]);
    }

    $good_format_email_for_query = "'" . addslashes($email) . "'";
    $encoded = urlencode($good_format_email_for_query);

    $url = URL . QUERY_LEAD . "+WHERE+Email=$encoded";

    return get_salesforce_data($url);
}

function register_salesforce_lead(array $data)
{
    $post_url = URL . 'sobjects/Lead/';

    return post_salesforce_data($post_url, $data);
}


function update_salesforce_lead(string $lead_id, array $data)
{
    $post_url = URL . "sobjects/Lead/$lead_id";

    return patch_salesforce_data($post_url, $data);
}


function deleting_lead_on_salesforce(string $lead_id)
{
    $delete_url = URL . "sobjects/Lead/$lead_id";

    return delete_salesforce_data($delete_url);
}
