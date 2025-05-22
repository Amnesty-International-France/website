<?php
/**
 * The base configuration for WordPress
 *
 * This config is specially designed for Clever Cloud
 * All the env vars defined here must be set in the clever cloud web ui
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 */

// ** Base URL settings ** //
define('WP_HOME', getenv('WP_HOME'));
define('WP_SITEURL', getenv('WP_SITEURL'));

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv('MYSQL_ADDON_DB'));

/** Database username */
define('DB_USER', getenv('MYSQL_ADDON_USER'));

/** Database password */
define('DB_PASSWORD', getenv('MYSQL_ADDON_PASSWORD'));

/** Database hostname */
define('DB_HOST', getenv('MYSQL_ADDON_HOST') . ':' . getenv('MYSQL_ADDON_PORT'));

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', getenv('WP_AUTH_KEY'));
define('SECURE_AUTH_KEY', getenv('WP_SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY', getenv('WP_LOGGED_IN_KEY'));
define('NONCE_KEY', getenv('WP_NONCE_KEY'));
define('AUTH_SALT', getenv('WP_AUTH_SALT'));
define('SECURE_AUTH_SALT', getenv('WP_SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT', getenv('WP_LOGGED_IN_SALT'));
define('NONCE_SALT', getenv('WP_NONCE_SALT'));
define('WP_CACHE_KEY_SALT', getenv('WP_CACHE_KEY_SALT'));


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/* Add any custom values between this line and the "stop editing" line. */

// auto-update
define('WP_AUTO_UPDATE_CORE', true);

# https://www.clever-cloud.com/developers/guides/tutorial-wordpress/#ssl-configuratio
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
	$_SERVER['HTTPS'] = 'on';
} elseif (isset($_SERVER['X_FORWARDED_PROTO']) && $_SERVER['X_FORWARDED_PROTO'] == 'https') {
	$_SERVER['HTTPS'] = 'on';
}

if (!defined('WP_ENVIRONMENT_TYPE') && getenv('WP_ENVIRONMENT_TYPE')) {
	define('WP_ENVIRONMENT_TYPE', getenv('WP_ENVIRONMENT_TYPE'));
}

if (getenv('WP_ENVIRONMENT_TYPE') === 'development') {
	#
	define('JETPACK_DEV_DEBUG', true);
}

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
