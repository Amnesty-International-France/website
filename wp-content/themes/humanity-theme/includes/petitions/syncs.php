<?php

class Sync_Command {

	public function import_users() {
		$response = get_salesforce_users();
		insert_users_records( $response );
		while( isset($response['nextRecordsUrl']) ) {
			$response = get_salesforce_users_query( substr( $response['nextRecordsUrl'], 1 ) );
			insert_users_records( $response );
		}
	}

	public function import_signatures() {
		$petition_ids = new WP_Query([
			'post_type' => 'petition',
			'posts_per_page' => -1,
			'fields' => 'ids',
		]);

		foreach( $petition_ids->posts as $post_id ) {
			$end_date = get_field('date_de_fin', $post_id);

			if ( isset($end_date) && ( strtotime(date( 'Y-m-d' )) <= strtotime($end_date) ) ) {
				$uidsf = get_field( 'uidsf', $post_id );

				$encoded_query = urlencode( sprintf(
					"SELECT Ext_ID_WP__c, Petition__r.Ext_ID_Petition__c, Email__c, Date_signature_petition__c, Code_Marketing_Prestataire__c, Message__c FROM Signature_de_petition__c WHERE Petition__r.Ext_ID_Petition__c = '%s'",
					esc_sql( $uidsf )
				));

				$response = get_salesforce_data("services/data/v57.0/query/?q={$encoded_query}");

				if( ! $response ) {
					continue;
				}

				foreach( $response['records'] as ['Ext_ID_WP__c' => $petition_id, 'Petition__r' => $petition_r, 'Email__c' => $email, 'Date_signature_petition__c' => $date, 'Code_Marketing_Prestataire__c' => $code_origine, 'Message__c' => $message] ) {
					if( empty( $petition_id ) ) {
						$petition_id = get_petition_id_from_uidsf( $petition_r['Ext_ID_Petition__c'] );
					}

					$user = get_local_user( $email );
					if( ! $user ) {
						continue;
					}

					if( have_signed( $petition_id, $user->id) ) {
						continue;
					}

					insert_petition_signature( $petition_id, $user->id, $date, $code_origine, $message, 0, 1, date('Y-m-d') );
				}
			}
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
			WP_CLI::error( 'No signatures to sync' );
			return;
		}

		sync_signatures_to_salesforce( $signatures_to_sync );
	}

	public function signatures_failed() {
		$signatures_to_sync = get_failed_signatures_to_sync();
		if( empty( $signatures_to_sync ) ) {
			WP_CLI::error( 'No failed signatures to sync' );
			return;
		}

		sync_signatures_to_salesforce( $signatures_to_sync );
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

function get_petition_id_from_uidsf( $uidsf ) {
	$args = array(
		'post_type' => 'petition',
		'posts_per_page' => 1,
		'fields' => 'ids',
		'meta_query' => array(
			array(
				'key' => 'uidsf',
				'value' => $uidsf,
				'compare' => '=',
			),
		),
	);

	$posts_found = get_posts( $args );

	if ( ! empty( $posts_found ) ) {
		return $posts_found[0];
	}

	return null;
}

WP_CLI::add_command( 'sync', new Sync_Command() );
