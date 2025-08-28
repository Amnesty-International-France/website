<?php

declare(strict_types=1);

function aif_create_action_urgent_tables() {
	global $wpdb;

	$table_prefix    = $wpdb->prefix;
	$charset_collate = $wpdb->get_charset_collate();

	$table_name_action_urgent = $wpdb->prefix . 'aif_urgent_action';

	$sql = "CREATE TABLE $table_name_action_urgent (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id INT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		is_sent TINYINT(1) NOT NULL,
		type VARCHAR(50) NOT NULL,
		PRIMARY KEY  (id)
	) ENGINE = InnoDB $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

add_action( 'after_setup_theme', 'aif_create_action_urgent_tables' );

function aif_create_table_once() {

	if ( ! get_option( 'aif_table_urgent_action_created' ) ) {
	error_log( 'je passe ici create table once dans if' );
		aif_create_action_urgent_tables();
		update_option( 'aif_table_urgent_action_created', 1 );
	}
}

function urgent_action_already_signed( $action_type, $user_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'aif_urgent_action';

	$sql = $wpdb->prepare(
		'SELECT * FROM %i WHERE type = %d AND user_id = %d',
		$table_name,
		$action_type,
		$user_id
	);

	return ! is_null( $wpdb->get_row( $sql ) );
}

function insert_urgent_action( $action_type, $user_id, $date, $is_sent ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'aif_urgent_action';

	$insert = $wpdb->insert(
		$table_name,
		[
			'type'       => $action_type,
			'user_id'    => $user_id,
			'created_at' => $date,
			'is_sent'    => $is_sent,
		],
		[
			'%s',
			'%d',
			'%s',
			'%d',
		]
	);

	return $insert !== false;
}
