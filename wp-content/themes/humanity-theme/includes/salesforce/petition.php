<?php

function get_salesforce_petition( string $id ) {
	$url = "services/data/v57.0/sobjects/Petition__c/$id";
	return get_salesforce_data( $url );
}

function get_salesforce_petition_by_ext_id( string $ext_id ) {
	$url = "services/data/v57.0/query/?q=SELECT+Id+FROM+Petition__c+WHERE+Ext_ID_Petition__c+=+'$ext_id'";
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
