<?php
if (! empty(getenv('WP_HOME'))) {
    define('WP_HOME', getenv('WP_HOME'));
}
if (! empty(getenv('WP_SITEURL'))) {
    define('WP_SITEURL', getenv('WP_SITEURL'));
}

// auto-update
define( 'WP_AUTO_UPDATE_CORE', true);

# https://www.clever-cloud.com/developers/guides/tutorial-wordpress/#ssl-configuratio
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
} elseif (isset($_SERVER['X_FORWARDED_PROTO']) && $_SERVER['X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// database config from env variables
if (! empty(getenv('MYSQL_ADDON_HOST')) && ! empty(getenv('MYSQL_ADDON_PORT'))) {
    define('DB_HOST', getenv('MYSQL_ADDON_HOST').':'.getenv('MYSQL_ADDON_PORT'));
}
if (! empty(getenv('MYSQL_ADDON_DB'))) {
    define('DB_NAME', getenv('MYSQL_ADDON_DB'));
}
if (! empty(getenv('MYSQL_ADDON_USER'))) {
    define('DB_USER', getenv('MYSQL_ADDON_USER'));
}
if (! empty(getenv('MYSQL_ADDON_PASSWORD'))) {
    define('DB_PASSWORD', getenv('MYSQL_ADDON_PASSWORD'));
}
