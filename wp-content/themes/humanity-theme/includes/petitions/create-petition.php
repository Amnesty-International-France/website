<?php

function create_petition( int $post_id ) {
	$post = get_post( $post_id );

	if( $post->post_type !== 'petition') {
		return;
	}

	$ext_id = get_field('uidsf', $post_id);

	if( $ext_id ) {
		return;
	}

	$terms = get_the_terms( $post, 'combat' );

	$data = [
		'Name' => $post->post_title,
		'RecordTypeId' => '0121o000000kdzmAAA',
		'Date_de_cloture__c' => (new DateTime(get_field('date_de_fin', $post_id)))->format('Y-m-d'),
		'Description_de_la_petition__c' => $post->post_excerpt,
		'Lien_petition__c' => get_post_permalink($post_id),
		'Type_action__c' => get_field('type', $post_id)['value'] === 'petition' ? 'Pétition' : 'Action de soutien',
		'Combats__c' => $terms && !is_wp_error($terms) && count($terms) > 0 ? $terms[0]->name : '',
	];
	$response = post_salesforce_petition( $data );
	if( $response !== false ) {
		update_field( 'sfid', $response, $post_id );
		$sf_obj = get_salesforce_petition( $response );
		if( $sf_obj !== false ) {
			update_field( 'uidsf', $sf_obj['Ext_ID_Petition__c'], $post_id );
			update_field( 'code_origine', $sf_obj['Code_defaut__c'], $post_id );
		}
	}
}

add_action( 'acf/save_post', 'create_petition', 20 );

function update_petition_end_date( $post_id ) {
	$post = get_post( $post_id );

	if( $post->post_type !== 'petition') {
		return;
	}

	$new_value = get_field( 'date_de_fin', $post_id );

	if ( empty($new_value) ) {
		return;
	}

	$ext_id = get_field('uidsf', $post_id);

	if( ! $ext_id ) {
		return;
	}

	patch_salesforce_petition( $ext_id, [
		'Date_de_cloture__c' => (new DateTime($new_value))->format('Y-m-d'),
	]);
}

add_action('acf/save_post', 'update_petition_end_date', 20);
