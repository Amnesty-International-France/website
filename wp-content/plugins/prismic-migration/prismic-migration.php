<?php
/**
 * Plugin Name:       Prismic Migration
 * Description:       Import data from a Prismic Repository to WordPress with humanity-theme
 * Version:           1.0.0
 * Author:            Les Tilleuls (valentin.dassonville@les-tilleuls.coop)
 */

if( ! defined( 'ABSPATH' ) ) exit;

if( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/Type.php';
	require_once __DIR__ . '/FileUploader.php';
	require_once __DIR__ . '/PrismicFetcher.php';
	require_once __DIR__ . '/TaxMapper.php';
	require_once __DIR__ . '/utils/PageUtils.php';
	require_once __DIR__ . '/utils/ImageDescCaptionUtils.php';
	require_once __DIR__ . '/utils/LinksUtils.php';
	require_once __DIR__ . '/transformers/transformers-loader.php';
    require_once __DIR__ . '/blocks/mappers-loader.php';
	require_once __DIR__ . '/PrismicMigrationCli.php';
	require_once __DIR__ . '/RepairLinksCli.php';
    WP_CLI::add_command( 'prismic-migration', 'PrismicMigrationCli' );
	WP_CLI::add_command( 'repair-links', 'RepairLinksCli' );
}
