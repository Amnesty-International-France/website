<?php

function get_salesforce_petition( string $id ) {
	$url = "services/data/v57.0/sobjects/Petition__c/$id";
	return get_salesforce_data( $url );
}

function post_salesforce_petition( array $data ): string|false {
	$url = 'services/data/v57.0/sobjects/Petition__c/';
	$response = post_salesforce_data( $url, $data );
	if( $response !== false && $response['success'] === true ) {
		return $response['id'];
	}
	return false;
}

function patch_salesforce_petition( string $external_id, array $data ): bool {
	$url = "services/data/v57.0/sobjects/Petition__c/Ext_ID_Petition__c/$external_id";
	return patch_salesforce_data( $url, $data );
}

function get_salesforce_petition_counter( string $ext_id ) {
	$url = "services/data/v57.0/query/?q=SELECT+Nb_signatures_total__c+FROM+Petition__c+WHERE+Ext_ID_Petition__c+=+'$ext_id'";
	return get_salesforce_data( $url );
}

function sync_signatures_to_salesforce( $signatures ) {
	// Create bulk job
	$job = create_bulk_job_signatures();
	if( ! $job ) {
		WP_CLI::error( 'Error creating bulk job' );
		return;
	}

	$job_id = $job['id'];
	$url_upload = $job['contentUrl'];

	// Prepare data
	$csv_data = prepare_bulk_data( $signatures );

	if ( ! upload_bulk_data( $url_upload, $csv_data ) ) {
		WP_CLI::error( 'Error uploading bulk data' );
		return;
	}

	// Launch the job
	if ( ! close_bulk_job( $job_id ) ) {
		WP_CLI::error( 'Error closing bulk job' );
		return;
	}

	// Set all signatures in pending
	foreach( $signatures as $signature ) {
		update_signature_status( $signature['petition_id'], $signature['user_id'], 1, $signature['is_synched'], $signature['last_sync'] );
	}

	// Job is processing
	poll_job_state( $job_id );

	// Results
	process_job_results( $job_id );
}

function create_bulk_job_signatures() {
	$url = "services/data/v57.0/jobs/ingest";
	return post_salesforce_data( $url, [
		'object' => 'Signature_de_petition__c',
		'operation' => 'insert',
	] );
}

function prepare_bulk_data( $signatures ) {
	$csv_header = ['Ext_ID_WP__c', 'Petition__r.Ext_ID_Petition__c', 'Civilite__c', 'Prenom__c', 'Nom__c',
		'Email__c', 'Date_signature_petition__c', 'Pays__c', 'Code_Postal__c',
		'Mobile__c', 'Type_signature__c', 'Code_Marketing_Prestataire__c', 'Lien_annulation__c', 'Message__c'];

	$csv_data = '"' . implode('","', $csv_header) . '"' . "\n";
	foreach( $signatures as $signature ) {
		$uidsf = get_field( 'uidsf', $signature['petition_id'] );
		$line = [$signature['petition_id'], $uidsf, $signature['civility'], $signature['firstname'], $signature['lastname'],
			$signature['email'], $signature['date_signature'], $signature['country'], $signature['postal_code'],
			$signature['phone'], 'W', $signature['code_origine'], '', $signature['message'] ?? ''];
		$csv_data .= '"' . implode('","', $line) . '"' . "\n";
	}

	return $csv_data;
}

function upload_bulk_data( $url_upload, $data ) {
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}

	$response = wp_remote_request(getenv('AIF_SALESFORCE_URL') . $url_upload, array(
		'method'    => 'PUT',
		'body'      => $data,
		'timeout'   => 60,
		'headers'   => array(
			'Content-Type' => 'text/csv',
			'Authorization' => 'Bearer ' . $access_token
		),
	));

	if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 300) {
		echo 'Erreur de requête Salesforce : ' . $response->get_error_message() . PHP_EOL;;
		return false;
	}
	return true;
}

function close_bulk_job( $job_id ) {
	$url = "services/data/v57.0/jobs/ingest/$job_id";
	return patch_salesforce_data( $url, [
		'state' => 'UploadComplete',
	] );
}

const SECONDS_BETWEEN_CHECKS = 30;

function poll_job_state( $job_id ) {
	$ecoule = 0;
	$state = '';

	while( ! in_array($state, ['JobComplete', 'Aborted', 'Failed']) ) {
		sleep(SECONDS_BETWEEN_CHECKS);
		$ecoule += SECONDS_BETWEEN_CHECKS;

		$access_token = get_salesforce_access_token();

		if (is_wp_error($access_token)) {
			WP_CLI::error( 'Erreur : ' . $access_token->get_error_message() );
			return;
		}

		$response = wp_remote_get(getenv("AIF_SALESFORCE_URL") . "services/data/v57.0/jobs/ingest/$job_id", array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token
			),
			'timeout' => 30,
		));

		if ( ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200 ) {
			$job_data = json_decode(wp_remote_retrieve_body($response), true);
			$state = $job_data['state'];
			WP_CLI::log( "Statut du job {$job_id} après {$ecoule}s : {$state}" );
		} else {
			WP_CLI::error( 'Error getting bulk job status' );
			break;
		}
	}
}

function process_job_results( $job_id ) {
	$success = get_bulk_success_results( $job_id );
	foreach( $success as $result ) {
		$petition_id = $result['Ext_ID_WP__c'];
		$user_id = get_local_user( $result['Email__c'] )->id;
		update_signature_status( $petition_id, $user_id, 0, 1, date('Y-m-d H:i:s'), true );
	}

	$failed = get_bulk_failed_results( $job_id );
	foreach( $failed as $result ) {
		$petition_id = $result['Ext_ID_WP__c'];
		$user_id = get_local_user( $result['Email__c'] )->id;
		update_signature_status( $petition_id, $user_id, 0, 0, date('Y-m-d H:i:s'), true );
	}

	$unprocessed = get_bulk_unprocessed_results( $job_id );
	foreach( $unprocessed as $result ) {
		$petition_id = $result['Ext_ID_WP__c'];
		$user_id = get_local_user( $result['Email__c'] )->id;
		update_signature_status( $petition_id, $user_id, 0, 0, date('Y-m-d H:i:s') );
	}
}

function get_bulk_success_results( $job_id ) {
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}

	$url = getenv('AIF_SALESFORCE_URL') . "services/data/v57.0/jobs/ingest/$job_id/successfulResults";
	$reponse = wp_remote_get( $url, ['headers' => ['Authorization' => 'Bearer ' . $access_token], 'timeout' => 30] );
	if (!is_wp_error($reponse) && wp_remote_retrieve_response_code($reponse) == 200) {
		$csv_parser = new CsvParser(wp_remote_retrieve_body($reponse));
		return $csv_parser->getRows();
	}
	return false;
}

function get_bulk_failed_results( $job_id ) {
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}

	$url = getenv('AIF_SALESFORCE_URL') . "services/data/v57.0/jobs/ingest/$job_id/failedResults";
	$reponse = wp_remote_get( $url, ['headers' => ['Authorization' => 'Bearer ' . $access_token], 'timeout' => 30] );
	if (!is_wp_error($reponse) && wp_remote_retrieve_response_code($reponse) == 200) {
		$csv_parser = new CsvParser(wp_remote_retrieve_body($reponse));
		return $csv_parser->getRows();
	}
	return false;
}

function get_bulk_unprocessed_results( $job_id ) {
	$access_token = get_salesforce_access_token();

	if (is_wp_error($access_token)) {
		echo 'Erreur : ' . $access_token->get_error_message();
		return false;
	}

	$url = getenv('AIF_SALESFORCE_URL') . "services/data/v57.0/jobs/ingest/$job_id/unprocessedrecords";
	$reponse = wp_remote_get( $url, ['headers' => ['Authorization' => 'Bearer ' . $access_token], 'timeout' => 30] );
	if (!is_wp_error($reponse) && wp_remote_retrieve_response_code($reponse) == 200) {
		$csv_parser = new CsvParser(wp_remote_retrieve_body($reponse));
		return $csv_parser->getRows();
	}
	return false;
}

class CsvParser {
	private $rows = [];
	public function __construct($csv_string) {
		$lines = str_getcsv($csv_string, "\n", escape: "");
		if (count($lines) < 2) return;
		$header = str_getcsv(array_shift($lines), escape: "");
		foreach ($lines as $line) {
			if (empty(trim($line))) continue;
			$row_data = str_getcsv($line, escape: "");
			if (count($header) == count($row_data)) {
				$this->rows[] = array_combine($header, $row_data);
			}
		}
	}
	public function getRows() {
		return $this->rows;
	}
}
