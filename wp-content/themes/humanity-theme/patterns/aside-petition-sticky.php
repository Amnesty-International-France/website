<?php

/**
 * Title: Aside Petition Sticky Pattern
 * Description: Sticky aside block with image
 * Slug: amnesty/aside-petition-sticky
 * Inserter: no
 */

$punchline = get_field('punchline');
$recipient = get_field('destinataire');

$post_id = get_the_ID();

?>

<aside class="petition-aside">
  <div class="sticky-card">
    <div class="sticky-card-content">
      <div class="sticky-card-title">SIGNEZ LA PÉTITION</div>
      <p class="recipient">À <?php echo esc_html( $recipient ); ?></p>
      <div class="punchline-wrapper">
        <p class="punchline"><?php echo esc_html( $punchline ); ?></p>
        <div class="border"></div>
      </div>
      <form method="post" action="">
        <div class="email-section">
          <input class="email-input" type="email" name="user_email" placeholder="Email*" required>
          <input type="hidden" name="petition_id" value="<?php echo esc_attr( $post_id ); ?>">
          <?php wp_nonce_field( 'amnesty_sign_petition', 'amnesty_petition_nonce' ); ?>
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
                      <div class="button-label">Signer</div>
                  </div>
              </button>
          </div>
          <p class="legals">Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty International France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable de traitement, traite et protège vos données personnelles collectées conformément aux dispositions de la Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi « Informatique et Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus, par email <span class="by-email"><a href="mailto:smd@amnesty.fr">smd@amnesty.fr</a></span> ou par téléphone 01 53 38 65 80. Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus d’information sur le traitement de vos données personnelles, veuillez consulter <span class="privacy-policy"><a href="/politique-de-confidentialité">notre politique de confidentialité</a></span></p>
        </div>
      </form>
    </div>
  </div>
</aside>
