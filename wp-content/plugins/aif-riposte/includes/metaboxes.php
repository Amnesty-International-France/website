<?php

/**
 * Riposte victory metabox.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}


function aif_riposte_register_meta(): void
{
	register_post_meta(
		'riposte_victory',
		'aif_riposte_date',
		[
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'aif_riposte_sanitize_date_meta',
			'auth_callback'     => static function (): bool {
				return current_user_can('edit_posts');
			},
		]
	);
    register_post_meta(
        'riposte_victory',
        'aif_riposte_external_url',
        [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback'     => static function (): bool {
                return current_user_can('edit_posts');
            },
        ]
    );
}
add_action('init', 'aif_riposte_register_meta');

function aif_riposte_sanitize_date_meta(mixed $value): string
{
	$value = sanitize_text_field((string) $value);

	if ('' === $value) {
		return '';
	}

	return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
}
