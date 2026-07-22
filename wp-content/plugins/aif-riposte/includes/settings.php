<?php

/**
 * Riposte settings page.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('admin_menu', 'aif_riposte_add_settings_page');

/**
 * Add settings submenu.
 *
 * @return void
 */
function aif_riposte_add_settings_page(): void
{
	add_submenu_page(
		'edit.php?post_type=riposte_victory',
		__('Réglages', 'aif-riposte'),
		__('Réglages', 'aif-riposte'),
		'manage_options',
		'aif_riposte_settings',
		'aif_riposte_settings_page_callback'
	);
}

/**
 * Render settings page.
 *
 * @return void
 */
function aif_riposte_settings_page_callback(): void
{
	if (
		isset($_POST['aif_riposte_settings_nonce'])
		&& wp_verify_nonce(
			sanitize_text_field(wp_unslash($_POST['aif_riposte_settings_nonce'])),
			'save_aif_riposte_settings'
		)
	) {
		aif_riposte_save_settings();
	}

	$chapo = (string) get_option('aif_riposte_archive_chapo', '');

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Réglages des Ripostes', 'aif-riposte'); ?></h1>

		<form method="post">

			<?php wp_nonce_field('save_aif_riposte_settings', 'aif_riposte_settings_nonce'); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="aif_riposte_archive_chapo">
							<?php esc_html_e('Texte du chapo', 'aif-riposte'); ?>
						</label>
					</th>
					<td>
						<textarea
							id="aif_riposte_archive_chapo"
							name="aif_riposte_archive_chapo"
							rows="8"
							class="large-text"
						><?php echo esc_textarea($chapo); ?></textarea>

						<p class="description">
							<?php esc_html_e('Texte affiché en haut de l’archive des ripostes.', 'aif-riposte'); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(__('Enregistrer', 'aif-riposte')); ?>

		</form>
	</div>
	<?php
}

/**
 * Save settings.
 *
 * @return void
 */
function aif_riposte_save_settings(): void
{
	if (! current_user_can('manage_options')) {
		return;
	}

	update_option(
		'aif_riposte_archive_chapo',
		wp_kses_post(
			wp_unslash($_POST['aif_riposte_archive_chapo'] ?? '')
		)
	);
}