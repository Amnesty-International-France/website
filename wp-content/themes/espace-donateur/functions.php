<?php

/**
 * Espace-donateur Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package espace-donateur
 */


/*
/ Configure Child Theme
*/

require_once realpath(__DIR__ . '/includes/child-theme/configure.php');


/*
/ Sales Force
*/


require_once realpath(__DIR__ . '/includes/sales-force/authentification.php');

require_once realpath(__DIR__ . '/includes/sales-force/user-data.php');
