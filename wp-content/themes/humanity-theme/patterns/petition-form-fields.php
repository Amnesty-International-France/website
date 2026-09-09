<?php

declare(strict_types=1);

/**
 * Title: Petition Actions
 * Description: Buttons action for petition clh card
 * Slug: amnesty/petition-form-fields
 * Inserter: no
 */

$civility = $civility ?? 'M.';

$field_id_suffix = $field_id_suffix ?? '-' . uniqid();
$civility_m_id     = 'civility_m' . $field_id_suffix;
$civility_mme_id   = 'civility_mme' . $field_id_suffix;
$civility_other_id = 'civility_other' . $field_id_suffix;

?>

<div class="tunnel-clh-email-step">
	<div class="email-section">
		<input class="email-input" type="email" name="user_email" placeholder="Email*"
		       required>
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
			<input class="firstname-input" type="text" name="user_firstname" placeholder="Prénom*">
		</div>

		<div class="lastname-section">
			<input class="lastname-input" type="text" name="user_lastname" placeholder="Nom*">
		</div>

		<div class="zipcode-and-country">
			<div class="zipcode-section">
				<input class="zipcode-input" type="text" name="user_zipcode" placeholder="Code postal*">
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

foreach ($countries as $country) :
    $country_name = get_the_title($country->ID);
    ?>
						<option
							value="<?php echo esc_attr($country_name); ?>" <?php if (esc_attr($country_name) === 'France') : ?> selected="selected"<?php endif; ?>>
							<?php echo esc_html(ucwords(strtolower($country_name))); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="phone-section">
			<input class="phone-input" type="tel" name="user_phone" placeholder="Téléphone">
		</div>
	</div>
</div>
