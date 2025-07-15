<?php

const BULK_SIZE = 50;

class Sync_Command {

	public function users() {
		$response = get_salesforce_users();
		insert_users_records( $response );
		while( isset($response['nextRecordsUrl']) ) {
			$response = get_salesforce_users_query( substr( $response['nextRecordsUrl'], 1 ) );
			insert_users_records( $response );
		}
	}

	public function compteurs() {
		$petition_ids = new WP_Query([
			'post_type' => 'petition',
			'posts_per_page' => -1,
			'fields' => 'ids',
		]);

		foreach( $petition_ids->posts as $post_id ) {
			$uidsf = get_field( 'uidsf', $post_id );
			if ( ! $uidsf ) {
				continue;
			}

			$petition = get_salesforce_petition_counter( $uidsf );
			if( ! $petition || $petition['totalSize'] === 0 ) {
				continue;
			}

			$signatures = $petition['records'][0]['Nb_signatures_total__c'];
			update_field( '_amnesty_signature_count', $signatures, $post_id);
		}
	}

	public function signatures() {
		$signatures_to_sync = get_signatures_to_sync();
		if( empty( $signatures_to_sync ) ) {
			return;
		}

		// TODO bulk signatures
	}
}

function insert_users_records( $response ) {
	if( isset( $response['records'] ) ) {
		global $wpdb;
		foreach ( $response['records'] as $record ) {
			$civility = $record['Salutation'] ?? '';
			$first_name = $record['FirstName'] ?? '';
			$last_name = $record['LastName'] ?? '';
			$email = $record['Email'] ?? '';
			$code_postal = $record['Code_Postal__c'] ?? '';
			$pays = $record['Pays__c'] ?? '';
			$mobile_phone = $record['MobilePhone'] ?? '';
			$data = [
				'firstname' => $first_name,
				'lastname' => $last_name,
				'email' => $email,
				'civility' => $civility,
				'country' => $pays,
				'postal_code' => $code_postal,
				'phone' => $mobile_phone,
			];
			$format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s'];
			$wpdb->insert( $wpdb->prefix . 'aif_users', $data, $format );
		}
	}
}

WP_CLI::add_command( 'sync', new Sync_Command() );
