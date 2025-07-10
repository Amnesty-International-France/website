<?php

class Sync_Command {

	public function users() {
		$response = get_salesforce_users();
		insert_records( $response );
		while( isset($response['nextRecordsUrl']) ) {
			$response = get_salesforce_users_query( substr( $response['nextRecordsUrl'], 1 ) );
			insert_records( $response );
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

			$petition_id = get_salesforce_petition_by_ext_id( $uidsf );
			if( ! $petition_id || $petition_id['totalSize'] === 0 ) {
				continue;
			}

			$petition = get_salesforce_petition( $petition_id['records'][0]['Id'] );
			if( $petition !== false ) {
				$signatures = $petition['Nb_signatures_total__c'];
				update_field( '_amnesty_signature_count', $signatures, $post_id);
			}
		}
	}
}

function insert_records( $response ) {
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
