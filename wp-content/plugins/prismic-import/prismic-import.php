<?php
/**
 * Plugin Name:       Import Prismic
 * Description:       Import data from a Prismic Repository to WordPress with humanity-theme
 * Version:           1.0.0
 * Author:            Les Tilleuls (valentin.dassonville@les-tilleuls.coop)
 * Requires PHP:      8.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI) {
    require_once __DIR__ . '/prismic-import-cli.php';
}