<?php

declare(strict_types=1);

function aif_create_inscription_nl_table()
{
    global $wpdb;

    $table_prefix    = $wpdb->prefix;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name_inscription_nl = $wpdb->prefix . 'aif_inscription_nl';

    $sql = "CREATE TABLE $table_name_inscription_nl (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id INT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		is_sent TINYINT(1) NOT NULL,
		type VARCHAR(50) NOT NULL,
		PRIMARY KEY  (id)
	) ENGINE = InnoDB $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('after_setup_theme', 'aif_create_table_once');

function aif_create_table_once()
{
    if (! get_option('aif_table_inscription_nl_created')) {
        aif_create_inscription_nl_table();
        update_option('aif_table_inscription_nl_created', 1);
    }
}
