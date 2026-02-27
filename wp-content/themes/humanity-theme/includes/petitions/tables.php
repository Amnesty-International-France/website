<?php

function aif_create_tables()
{
    global $wpdb;

    $table_prefix = $wpdb->prefix;
    $charset_collate = $wpdb->get_charset_collate();

    $table_name_users = $table_prefix . 'aif_users';
    $sql_users = "CREATE TABLE $table_name_users (
    	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    	firstname VARCHAR(100) NOT NULL,
    	lastname VARCHAR(100) NOT NULL,
    	email VARCHAR(255) NOT NULL,
    	civility VARCHAR(20),
    	country VARCHAR(100),
    	postal_code VARCHAR(20),
    	phone VARCHAR(50),
    	PRIMARY KEY (id),
    	UNIQUE KEY uq_email (email)
	) $charset_collate ENGINE=InnoDB;";

    $table_name_signatures = $table_prefix . 'aif_petitions_signatures';
    $wp_posts_table = $table_prefix . 'posts';
    $sql_signatures = "CREATE TABLE $table_name_signatures (
    	petition_id BIGINT(20) UNSIGNED NOT NULL,
    	user_id INT UNSIGNED NOT NULL,
    	date_signature DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    	pending TINYINT(1) NOT NULL DEFAULT 0,
    	is_synched TINYINT(1) NOT NULL DEFAULT 0,
    	last_sync DATETIME DEFAULT NULL,
    	nb_try INT UNSIGNED NOT NULL DEFAULT 0,
    	code_origine VARCHAR(255) NOT NULL,
    	message VARCHAR(32768),
    	PRIMARY KEY (petition_id, user_id),
    	CONSTRAINT fk_petition FOREIGN KEY (petition_id) REFERENCES $wp_posts_table(ID) ON DELETE CASCADE,
    	CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES $table_name_users(id) ON DELETE CASCADE
	) $charset_collate ENGINE=InnoDB;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_users);
    dbDelta($sql_signatures);
}

add_action('after_switch_theme', 'aif_create_tables');

function get_local_user($email)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_users';

    $sql = $wpdb->prepare(
        'SELECT * FROM %i WHERE email = %s',
        $table_name,
        $email
    );

    $user = $wpdb->get_row($sql);

    if (is_null($user)) {
        return false;
    }

    return $user;
}

function get_local_user_by_id($id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_users';

    $sql = $wpdb->prepare(
        'SELECT * FROM %i WHERE id = %d',
        $table_name,
        $id
    );

    $user = $wpdb->get_row($sql);

    if (is_null($user)) {
        return false;
    }

    return $user;
}

function insert_user($civility, $firstname, $lastname, $email, $country, $postal_code, $phone): int|false
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_users';

    $inserted = $wpdb->insert($table_name, [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'civility' => $civility,
        'country' => $country,
        'postal_code' => $postal_code,
        'phone' => $phone,
    ]);

    if ($inserted !== false) {
        return $wpdb->insert_id;
    }
    return false;
}

function have_signed($petitionId, $userId)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_petitions_signatures';

    $sql = $wpdb->prepare(
        'SELECT * FROM %i WHERE petition_id = %d AND user_id = %d',
        $table_name,
        $petitionId,
        $userId
    );

    return ! is_null($wpdb->get_row($sql));
}

function insert_petition_signature($petition_id, $user_id, $date_signature, $code_origine, $message, $pending = 0, $is_synched = 0, $last_sync = null, $nb_try = 0)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_petitions_signatures';

    $inserted = $wpdb->insert($table_name, [
        'petition_id' => $petition_id,
        'user_id' => $user_id,
        'date_signature' => $date_signature,
        'pending' => $pending,
        'is_synched' => $is_synched,
        'last_sync' => $last_sync,
        'nb_try' => $nb_try,
        'code_origine' => $code_origine,
        'message' => $message,
    ], ['%d', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%s']);

    return $inserted !== false;
}

function get_signatures_to_sync()
{
    global $wpdb;
    $users_table_name = $wpdb->prefix . 'aif_users';
    $signatures_table_name = $wpdb->prefix . 'aif_petitions_signatures';

    $query = $wpdb->prepare("SELECT * FROM $signatures_table_name s JOIN $users_table_name u ON(s.user_id = u.id) WHERE s.pending = %d AND s.is_synched = %d AND s.nb_try = %d LIMIT 20", [0, 0, 0]);

    return $wpdb->get_results($query, ARRAY_A);
}

function get_failed_signatures_to_sync()
{
    global $wpdb;
    $users_table_name = $wpdb->prefix . 'aif_users';
    $signatures_table_name = $wpdb->prefix . 'aif_petitions_signatures';

    $query = $wpdb->prepare("SELECT * FROM $signatures_table_name s JOIN $users_table_name u ON(s.user_id = u.id) WHERE s.pending = %d AND s.is_synched = %d AND s.nb_try = %d", [0, 0, 1]);

    return $wpdb->get_results($query, ARRAY_A);
}

function update_signature_status($petition_id, $user_id, $pending, $is_synched, $last_sync, $increment_nb_try = false)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aif_petitions_signatures';
    if ($increment_nb_try) {
        $query = $wpdb->prepare("UPDATE $table_name SET pending = %d, nb_try = nb_try + %d, is_synched = %d, last_sync = %s WHERE petition_id = %d AND user_id = %d", [$pending, 1, $is_synched, $last_sync, $petition_id, $user_id]);
    } else {
        $query = $wpdb->prepare("UPDATE $table_name SET pending = %d, is_synched = %d, last_sync = %s WHERE petition_id = %d AND user_id = %d", [$pending, $is_synched, $last_sync, $petition_id, $user_id]);
    }
    $wpdb->query($query);
}
