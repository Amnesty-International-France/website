<?php

$options = get_option(AIFRSS_OPTION_KEY);

?>

<div class="wrap">
	<h1>AIF RSS</h1>

	<form method="post" action="options.php">
		<?php
            settings_fields('aifrss_options_group');
do_settings_sections('aifrss_options_group');
?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="aifrss_feed_url">URL du flux RSS</label></th>
				<td><input name="<?php echo AIFRSS_OPTION_KEY; ?>[feed_url]" type="url" id="aifrss_feed_url" value="<?php echo esc_attr($options['feed_url']); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aifrss_items">Nombre maximum de communiqués à importer</label></th>
				<td><input name="<?php echo AIFRSS_OPTION_KEY; ?>[items]" type="number" id="aifrss_items" value="<?php echo esc_attr($options['items']); ?>" min="1" max="200" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aifrss_frequency">Fréquence</label></th>
				<td>
					<select name="<?php echo AIFRSS_OPTION_KEY; ?>[frequency]" id="aifrss_frequency">
						<option value="hourly" <?php selected($options['frequency'], 'hourly'); ?>>Chaque heure</option>
						<option value="twicedaily" <?php selected($options['frequency'], 'twicedaily'); ?>>Deux fois par jour</option>
						<option value="daily" <?php selected($options['frequency'], 'daily'); ?>>Une fois par jour</option>
						<option value="every_three_days" <?php selected($options['frequency'], 'every_three_days'); ?>>Tous les 3 jours</option>
						<option value="every_seven_days" <?php selected($options['frequency'], 'every_seven_days'); ?>>Une fois par semaine</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aifrss_post_status">Statut des communiqués créés</label></th>
				<td>
					<select name="<?php echo AIFRSS_OPTION_KEY; ?>[post_status]" id="aifrss_post_status">
						<option value="publish" <?php selected($options['post_status'], 'publish'); ?>>Publié</option>
						<option value="draft" <?php selected($options['post_status'], 'draft'); ?>>Brouillon</option>
					</select>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<h2>Actions</h2>
	<p>
		<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=aifrss_run_now'), 'aifrss_run_now_nonce'); ?>" class="button button-primary">Exécuter l'import maintenant</a>
	</p>

	<h2>Etat</h2>
	<?php
$last_run = get_option('aifrss_last_run');
$last_count = get_option('aifrss_last_count') ?? 0;
$last_error = get_option('aifrss_last_error');
?>
	<p>Dernière exécution : <?php echo $last_run ? esc_html($last_run) : 'Jamais'; ?> (<?= intval($last_count) ?> communiqués importés)</p>
	<?php if ($last_error) : ?>
		<p style="color:crimson;">Dernière erreur : <?php echo esc_html($last_error); ?></p>
	<?php endif; ?>
</div>
