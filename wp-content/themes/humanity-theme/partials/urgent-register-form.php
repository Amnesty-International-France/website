<?php

declare(strict_types=1);

$countries = get_posts(
    [
        'post_type'      => 'fiche_pays',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]
);

?>

<div class="urgent-register">
	<div class="urgent-register-header">
		<p>
			<?php echo esc_attr($text_header ?? ''); ?>
		</p>
	</div>
	<div class="urgent-register-form">
		<form id="urgent-register" method="post" action="">
			<div class="form-mess hidden"></div>
			<div class="urgent-register-form-input">
				<?php
                foreach ($input ?? [] as $item) :
                    $item_esc    = esc_attr($item);
                    $placeholder = 'tel' === $item ? 'Téléphone mobile' : $item
                    ?>
					<label for="<?php echo $item_esc; ?>"></label>
					<input class="input"
							name="<?php echo $item_esc; ?>"
							type="<?php echo $item_esc; ?>"
							placeholder="<?php echo esc_attr(ucfirst($placeholder)); ?>"
					required/>
					<div class="input-error hidden"></div>
				<?php endforeach; ?>
				<div class="additional-form hidden">
					<div class="form-group civility">
						<label class="civility-label">Civilité :</label>
						<div class="civilities">
							<label for="civility_m">M.</label>
							<input type="radio" id="civility_m"
									name="civility"
									value="M."
									checked>
							<label for="civility_mme">Mme</label>
							<input type="radio" id="civility_mme"
									name="civility"
									value="Mme" >
							<label for="civility_other">Autre</label>
							<input type="radio" id="civility_other"
									name="civility"
									value="Autre" >
						</div>
						<div class="input-error-civility hidden"></div>
					</div>
					<div class="form-group">
						<label for="lastname"></label>
						<input type="text" id="lastname" name="lastname"
								placeholder="Nom" required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-group">
						<label for="firstname"></label>
						<input type="text" id="firstname" name="firstname"
								placeholder="Prénom" required>
						<div class="input-error hidden"></div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="zipcode"></label>
							<input type="text"
									id="zipcode"
									name="zipcode"
									placeholder="Code Postal"
									required>
							<div class="input-error hidden"></div>
						</div>
						<div class="form-group country-selection">
							<select class="country-input " name="country">
								<option value=""><?php _e('Pays*', 'textdomain'); ?></option>
								<?php
                                foreach ($countries as $country) :
                                    $country_name = get_the_title($country->ID);
                                    ?>
									<option value="<?php echo esc_attr($country_name); ?>"
										<?php
                                        if (esc_attr($country_name) === 'France') :
                                            ?>
											selected="selected"
											<?php
                                        endif;
                                    ?>
									>
										<?php echo esc_html(ucwords(strtolower($country_name))); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<?php if (! in_array('tel', $input ?? [], true)) : ?>
						<div class="form-group">
							<label for="tel"></label>
							<input
								id="tel"
								name="tel"
								type="tel"
								placeholder="<?php esc_attr_e('Téléphone mobile', 'text-domain'); ?>"
							>
							<div class="input-error hidden"></div>
						</div>
					<?php endif; ?>
				</div>
				<div class="form-group">
					<input type="hidden" name="type" value="<?php echo esc_attr($action_type ?? ''); ?>">
				</div>
			</div>
			<div class="urgent-register-form-cta">
				<button type="submit" name="sign_urgent_action">
					<?php
                    echo file_get_contents(get_template_directory() . '/assets/images/icon-arrow.svg');
?>
					S'inscrire
				</button>
			</div>
		</form>
		<div class="urgent-action-legend">
			<p>Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty International France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable de traitement, traite et protège vos données personnelles collectées conformément aux dispositions de la Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi « Informatique et Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus, par email smd@amnesty.fr ou par téléphone 01 53 38 65 80. Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus d’information sur le traitement de vos données personnelles, veuillez consulter notre politique de confidentialité</p>
		</div>
	</div>
</div>
