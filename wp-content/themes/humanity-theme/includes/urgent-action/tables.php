<?php

declare(strict_types=1);

// À modifier (ex: passer à '1.2') à chaque changement de structure de la table
const NEW_AU_TABLE_VERSION = '1.1';

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
		thematique VARCHAR(50) NULL,
		PRIMARY KEY (id, user_id, type),
		CONSTRAINT fk_ua_user FOREIGN KEY (user_id) REFERENCES $table_name_users(id) ON DELETE CASCADE
	) $charset_collate ENGINE = InnoDB;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('after_switch_theme', 'aif_create_action_urgent_tables');

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('table-urgent-action', 'aif_create_action_urgent_tables');
    WP_CLI::add_command('update-db-schema', 'aif_update_schema_action_urgent_tables');
}

function aif_update_schema_action_urgent_tables()
{
    $current_db_version = get_option('current-aif-action-urgente-version');

    if ($current_db_version != NEW_AU_TABLE_VERSION) {
        aif_create_action_urgent_tables();

        update_option('current-aif-action-urgente-version', NEW_AU_TABLE_VERSION);
        WP_CLI::success('Schéma mis à jour vers la version '. NEW_AU_TABLE_VERSION);
    } else {
        WP_CLI::info('La table est déjà à la version '. NEW_AU_TABLE_VERSION);
    }
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

function insert_urgent_action($action_type, $user_id, $date, $is_sent, $thematique = null)
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
            'thematique' => $thematique,
        ],
        [
            '%s',
            '%d',
            '%s',
            '%d',
            '%s',
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
