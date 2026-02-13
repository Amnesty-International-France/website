<?php

/**
 * Title: Aside Petition Sticky Pattern
 * Description: Sticky aside block with image
 * Slug: amnesty/aside-petition-sticky
 * Inserter: no
 */

$type = get_field('type')['value'] ?? 'petition';
if ($type === 'action-soutien') {
    $enable_user_message = get_field('autoriser_message_utilisateur');
    $phone_required = get_field('phone_required');
    $form_contenu = get_field('form_contenu');
    $button_text = get_field('button_text');
    $comment_max_length = (int)get_field('comment_max_length');
    $terms = get_field('terms');
} else {
    $punchline = get_field('punchline');
    $recipient = get_field('destinataire');
}

$current_date = date('Y-m-d');
$end_date = get_field('date_de_fin');
$post_id = get_the_ID();

$civility = $civility ?? 'M.';
?>

<aside class="petition-aside">
  <div class="sticky-card">
    <div class="sticky-card-content">
      <div class="sticky-card-title">SIGNEZ LA PÉTITION</div>
      <p class="recipient"><?php echo esc_html($recipient ?? ''); ?><?php echo esc_html($form_contenu ?? ''); ?></p>
        <?php if ($type === 'petition') : ?>
      <div class="punchline-wrapper">
        <p class="punchline"><?php echo esc_html($punchline ?? ''); ?></p>
        <div class="border"></div>
      </div>
        <?php endif; ?>
		<?php if (isset($end_date) && (strtotime($end_date) >= strtotime($current_date))) : ?>

      <form class="signature-petition-form" method="post" action="">
          <div class="cf-turnstile" data-sitekey="<?php echo esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
          <?php if ($type === 'action-soutien' && $enable_user_message) : ?>
          <div class="message-section">
              <textarea class="message-input" type="text" name="user_message" placeholder="Votre message* (<?= $comment_max_length ?> caractères max)" required maxlength="<?= $comment_max_length ?>"></textarea>
          </div>
          <?php endif; ?>
        <div class="email-section">
          <input class="email-input" type="email" name="user_email" placeholder="Email*" required>
          <input type="hidden" name="petition_id" value="<?php echo esc_attr($post_id); ?>">
        </div>

        <div class="full-form">
          <div class="form-group civility-section">
            <label class="civility-label">Civilité :</label>
            <div class="civilities">
              <input type="radio" id="civility_m" name="civility" value="M." <?php echo ($civility === 'M.') ? 'checked' : ''; ?>>
              <label for="civility_m">M.</label>
              <input type="radio" id="civility_mme" name="civility" value="Mme" <?php echo ($civility === 'Mme') ? 'checked' : ''; ?>>
              <label for="civility_mme">Mme</label>
              <input type="radio" id="civility_other" name="civility" value="Autre" <?php echo ($civility === 'Autre') ? 'checked' : ''; ?>>
              <label for="civility_other">Autre</label>
            </div>
          </div>

          <div class="firstname-section">
            <input class="firstname-input" type="text" name="user_firstname" placeholder="Prénom*">
            <input type="hidden" name="petition_id" value="<?php echo esc_attr($post_id); ?>">
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
                $countries = get_posts([
                    'post_type' => 'fiche_pays',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC',
                ]);

		    foreach ($countries as $country) :
		        $country_name = get_the_title($country->ID);
		        ?>
                <option value="<?php echo esc_attr($country_name); ?>" <?php if (esc_attr($country_name) === 'France') :?> selected="selected"<?php endif;?>>
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

        <div class="sign-and-legals">
          <div class="custom-button-block center">
              <button type="submit" name="sign_petition" class="custom-button">
                  <div class='content bg-yellow medium'>
                      <div class="icon-container">
                          <svg
                              xmlns="http://www.w3.org/2000/svg"
                              fill="none"
                              viewBox="0 0 24 24"
                              strokeWidth="1.5"
                              stroke="currentColor"
                          >
                              <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                          </svg>
                      </div>
                      <div class="button-label"><?php if (isset($button_text) && ! empty($button_text)) : ?><?php echo esc_html($button_text) ?><?php else : ?>Signer<?php endif;?></div>
                  </div>
              </button>
          </div>
          <div class="legals">
              <?php if (isset($terms) && ! empty($terms)) : ?>
                  <?php the_field('terms'); ?>
              <?php else : ?>
                  <p>Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty International France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable de traitement, traite et protège vos données personnelles collectées conformément aux dispositions de la Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi « Informatique et Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus, par email <span class="by-email"><a href="mailto:smd@amnesty.fr">smd@amnesty.fr</a></span> ou par téléphone 01 53 38 65 80. Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus d’information sur le traitement de vos données personnelles, veuillez consulter <span class="privacy-policy"><a href="/politique-de-confidentialite">notre politique de confidentialité</a></span></p>
              <?php endif; ?>
          </div>
        </div>
      </form>
		<?php else : ?>
		<div class="close-petition">
			<p>Cette pétition est <strong>terminée</strong>, merci pour votre soutien.</p>
			<p>N'hésitez pas à signer une des autres pétitions disponibles.</p>
			<div class="custom-button-block center">
				<a href="/petitions" class="custom-button">
					<div class='content outline-black medium'>
						<div class="button-label">Toutes nos pétitions</div>
					</div>
				</a>
			</div>
		</div>
		<?php endif; ?>
    </div>
  </div>
</aside>
