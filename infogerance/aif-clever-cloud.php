<?php
// auto-update
define( 'WP_AUTO_UPDATE_CORE', true);

# https://www.clever-cloud.com/developers/guides/tutorial-wordpress/#ssl-configuratio
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
} elseif (isset($_SERVER['X_FORWARDED_PROTO']) && $_SERVER['X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

