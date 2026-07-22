<?php

/**
 * Plugin assets.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', 'aif_riposte_front_assets');
add_action('admin_enqueue_scripts', 'aif_riposte_admin_assets');
add_action('enqueue_block_editor_assets', 'aif_riposte_editor_assets');

/**
 * Enqueue frontend assets.
 */
function aif_riposte_front_assets(): void
{
	if (! is_post_type_archive('riposte_victory')) {
		return;
	}

	wp_enqueue_style(
		'aif-riposte',
		AIF_RIPOSTE_URL . 'assets/css/aif-riposte.css',
		[],
		AIF_RIPOSTE_VERSION
	);

	wp_enqueue_script(
		'aif-riposte-load-more',
		AIF_RIPOSTE_URL . 'assets/js/load-more.js',
		[],
		AIF_RIPOSTE_VERSION,
		true
	);

	wp_localize_script(
		'aif-riposte-load-more',
		'aifRiposteLoadMore',
		[
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('aif_riposte_load_more'),
			'i18n'    => [
				'loading' => 'Chargement...',
			],
		]
	);
}

/**
 * Enqueue admin assets.
 *
 * @param string $hook Current admin page.
 */
function aif_riposte_admin_assets(string $hook): void
{
	$screen = get_current_screen();

	if (! $screen || 'riposte_victory' !== $screen->post_type) {
		return;
	}

	if ('edit.php' === $hook) {
		aif_riposte_enqueue_admin_ordering_assets();
	}
}

/**
 * Enqueue block editor assets.
 */
function aif_riposte_editor_assets(): void
{
	$screen = get_current_screen();

	if (! $screen || 'riposte_victory' !== $screen->post_type) {
		return;
	}

	wp_enqueue_script(
		'aif-riposte-editor',
		AIF_RIPOSTE_URL . 'assets/js/editor.js',
		[
			'wp-plugins',
			'wp-edit-post',
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-core-data',
		],
		AIF_RIPOSTE_VERSION,
		true
	);

	wp_enqueue_script(
		'aif-riposte-admin-theme',
		AIF_RIPOSTE_URL . 'assets/js/admin-theme.js',
		[ 'wp-data', 'wp-dom-ready' ],
		AIF_RIPOSTE_VERSION,
		true
	);
}

/**
 * Enqueue ordering assets on admin list.
 */
function aif_riposte_enqueue_admin_ordering_assets(): void
{
	wp_enqueue_script(
		'aif-riposte-admin-ordering',
		AIF_RIPOSTE_URL . 'assets/js/admin-ordering.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		AIF_RIPOSTE_VERSION,
		true
	);

	wp_localize_script(
		'aif-riposte-admin-ordering',
		'aifRiposteOrdering',
		[
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('aif_riposte_ordering'),
		]
	);

	wp_add_inline_style(
		'wp-admin',
		'
		.column-aif_riposte_order {
			width: 40px;
			text-align: center;
		}
		.aif-riposte-sort-handle {
			cursor: move;
			font-size: 18px;
			line-height: 1;
			color: #646970;
		}
		.aif-riposte-sort-placeholder {
			background: #f0f0f1;
			outline: 1px dashed #8c8f94;
			height: 48px;
		}
		'
	);
}