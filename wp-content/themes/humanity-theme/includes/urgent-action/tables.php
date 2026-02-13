<?php

declare(strict_types=1);

function aif_create_action_urgent_tables($args = [])
{
    global $wpdb;

    $table_prefix    = $wpdb->prefix;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name_action_urgent = $wpdb->prefix . 'aif_urgent_action';
    $table_name_users = $table_prefix . 'aif_users';

    $sql = "CREATE TABLE $table_name_action_urgent (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id INT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL,
		is_sent TINYINT(1) NOT NULL,
		type VARCHAR(50) NOT NULL,
		PRIMARY KEY (id, user_id, type),
		CONSTRAINT fk_ua_user FOREIGN KEY (user_id) REFERENCES $table_name_users(id) ON DELETE CASCADE
	) $charset_collate ENGINE = InnoDB;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('after_switch_theme', 'aif_create_action_urgent_tables');

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('table-urgent-action', 'aif_create_action_urgent_tables');
}

function urgent_action_already_signed($user_id, $action_type)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_urgent_action';

    $sql = $wpdb->prepare(
        'SELECT * FROM %i WHERE user_id = %d AND type = %s',
        $table_name,
        $user_id,
        $action_type,
    );

    return ! is_null($wpdb->get_row($sql));
}

function insert_urgent_action($action_type, $user_id, $date, $is_sent)
{
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


function get_unsynced_actions()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'aif_urgent_action';

    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE is_sent = %d",
        [0]
    );

    return $wpdb->get_results($sql, ARRAY_A);
}

function update_ua_syncs_with_sf(string $ua_id)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'aif_urgent_action';

    $sql = $wpdb->prepare(
        "UPDATE $table_name SET is_sent = %d WHERE id = %s",
        [1, $ua_id]
    );

    $wpdb->query($sql);
}

function delete_ua_synched()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'aif_urgent_action';

    $sql = "DELETE FROM $table_name WHERE is_sent = 1";

    $wpdb->query($sql);
}
