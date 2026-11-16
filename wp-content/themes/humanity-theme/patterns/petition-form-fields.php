<?php

declare(strict_types=1);

/**
 * Title: Petition Actions
 * Description: Buttons action for petition clh card
 * Slug: amnesty/petition-form-fields
 * Inserter: no
 */

$civility = $civility ?? 'M.';

// Values typed in a signature the server rejected; empty for a first display.
$form_values = $form_values ?? [];

// A value outside the radios (hand-crafted POST) checks none of them.
if (!empty($form_values['civility'])) {
    $civility = $form_values['civility'];
}

$field_id_suffix = $field_id_suffix ?? '-' . uniqid();
$civility_m_id     = 'civility_m' . $field_id_suffix;
$civility_mme_id   = 'civility_mme' . $field_id_suffix;
$civility_other_id = 'civility_other' . $field_id_suffix;

?>

<div class="tunnel-clh-email-step">
	<div class="email-section">
		<input class="email-input" type="email" name="user_email" placeholder="Email*"
		       value="<?php echo esc_attr($form_values['user_email'] ?? ''); ?>"
		       data-invalid-message="<?php echo esc_attr(AMNESTY_PETITION_INVALID_EMAIL_MESSAGE); ?>" required>
	</div>
	<div class="full-form">
		<div class="form-group civility-section">
			<label class="civility-label">Civilité :</label>
			<div class="civilities">
				<input type="radio" id="<?php echo esc_attr($civility_m_id); ?>" name="civility"
				       value="M." <?php echo ($civility === 'M.') ? 'checked' : ''; ?>>
				<label for="<?php echo esc_attr($civility_m_id); ?>">M.</label>
				<input type="radio" id="<?php echo esc_attr($civility_mme_id); ?>" name="civility"
				       value="Mme" <?php echo ($civility === 'Mme') ? 'checked' : ''; ?>>
				<label for="<?php echo esc_attr($civility_mme_id); ?>">Mme</label>
				<input type="radio" id="<?php echo esc_attr($civility_other_id); ?>" name="civility"
				       value="Autre" <?php echo ($civility === 'Autre') ? 'checked' : ''; ?>>
				<label for="<?php echo esc_attr($civility_other_id); ?>">Autre</label>
			</div>
		</div>

		<div class="firstname-section">
			<input class="firstname-input" type="text" name="user_firstname" placeholder="Prénom*"
			       value="<?php echo esc_attr($form_values['user_firstname'] ?? ''); ?>">
		</div>

		<div class="lastname-section">
			<input class="lastname-input" type="text" name="user_lastname" placeholder="Nom*"
			       value="<?php echo esc_attr($form_values['user_lastname'] ?? ''); ?>">
		</div>

		<div class="zipcode-and-country">
			<div class="zipcode-section">
				<input class="zipcode-input" type="text" name="user_zipcode" placeholder="Code postal*"
				       value="<?php echo esc_attr($form_values['user_zipcode'] ?? ''); ?>">
			</div>

			<div class="country-section">
				<select class="country-input " name="user_country">
					<option value=""><?php _e('Pays*', 'textdomain'); ?></option>
					<?php
                    $countries = get_transient('amnesty_fiche_pays_list');
if ($countries === false) {
    $countries = get_posts([
        'post_type' => 'fiche_pays',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    set_transient('amnesty_fiche_pays_list', $countries, HOUR_IN_SECONDS);
}

$country_names = array_map(static fn ($country) => get_the_title($country->ID), $countries);
// Titles are texturized (e.g. &#8217;), the kept value is the decoded text the browser posted.
$kept_country_index = array_search(
    $form_values['user_country'] ?? null,
    array_map(static fn ($name) => html_entity_decode($name, ENT_QUOTES, 'UTF-8'), $country_names),
    true
);
$selected_country = $kept_country_index === false ? 'France' : $country_names[$kept_country_index];

foreach ($country_names as $country_name) :
    ?>
						<option
							value="<?php echo esc_attr($country_name); ?>" <?php if ($country_name === $selected_country) : ?> selected="selected"<?php endif; ?>>
							<?php echo esc_html(ucwords(strtolower($country_name))); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="phone-section">
			<input class="phone-input" type="tel" name="user_phone" placeholder="Téléphone"
			       value="<?php echo esc_attr($form_values['user_phone'] ?? ''); ?>">
		</div>
	</div>
</div>
