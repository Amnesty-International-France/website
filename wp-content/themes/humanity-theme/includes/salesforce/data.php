<?php

function get_salesforce_data($url)
{
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}


	$response = wp_remote_get(getenv("AIF_SALESFORCE_URL") . $url, array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $access_token,
			'timeout' => 30,
		)
	));

	if (is_wp_error($response)) {
		echo 'Erreur de requête Salesforce : ' . $response->get_error_message() . PHP_EOL;
		return false;
	} else {
		$data = wp_remote_retrieve_body($response);
		return json_decode($data, true);
	}
}

function post_salesforce_data($url, $params = [])
{
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}
	$response = wp_remote_post(getenv("AIF_SALESFORCE_URL") . $url, array(
		'body'      => json_encode($params),
		'timeout'   => 30,
		'headers'   => array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $access_token
		),
	));

	if (is_wp_error($response)) {
		echo 'Erreur de requête Salesforce : ' . $response->get_error_message() . PHP_EOL;
		return false;
	} else {
		$data = wp_remote_retrieve_body($response);
		return json_decode($data, true);
	}
}

function patch_salesforce_data($url, $params = [])
{
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message() . PHP_EOL;
		return false;
	}

	$response = wp_remote_request(getenv("AIF_SALESFORCE_URL") . $url, array(
		'method'    => 'PATCH',
		'body'      => json_encode($params),
		'timeout'   => 30,
		'headers'   => array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $access_token
		),
	));

	if (is_wp_error($response)) {
		echo 'Erreur de requête Salesforce : ' . $response->get_error_message() . PHP_EOL;
		return false;
	} else {
		$data = wp_remote_retrieve_body($response);
		return json_decode($data);
	}
}
