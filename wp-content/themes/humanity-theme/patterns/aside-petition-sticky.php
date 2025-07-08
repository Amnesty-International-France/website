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
      <div class="decorative-icon-container">
        <svg class="decorative-icon" xmlns="http://www.w3.org/2000/svg" width="140" height="143" viewBox="0 0 140 143" fill="none">
          <path d="M111.851 55.981C110.675 53.6257 108.73 51.8484 106.045 50.6693C107.134 47.3517 108.073 41.7568 104.209 37.4305C100.702 33.5031 96.2719 32.8037 92.7226 33.2488C92.8198 30.2374 91.8274 26.7464 88.2065 24.2033C84.8402 21.8393 80.9649 21.6052 77.2697 23.5386C76.915 20.2151 75.7452 16.3137 72.4505 14.2705C67.085 10.9413 62.6833 12.1927 59.9348 13.8255C57.2807 15.4034 55.7477 17.5997 55.4617 18.0303L43.7612 34.7833L43.8241 34.8296C38.0554 39.2367 30.9853 44.7276 29.0605 46.609C23.389 52.149 25.4997 58.9548 25.5941 59.2409C25.6284 59.3478 25.6684 59.449 25.7171 59.5501C26.1346 60.4373 35.9446 81.2998 37.8037 84.707C39.7285 88.2328 39.8315 95.5559 39.7085 98.0817C39.7085 98.125 39.7056 98.1684 39.7056 98.2117V126.767H44.8537V98.2753C44.9166 96.917 45.2313 87.5507 42.3111 82.1986C40.618 79.0977 31.4915 59.7119 30.459 57.5214C30.2789 56.7873 29.6268 53.2934 32.6384 50.3543C35.1467 47.9065 48.9808 37.3929 54.5694 33.1939C55.6619 32.9078 59.4114 32.2546 62.6862 35.5636C66.676 39.5951 75.6251 48.2389 79.2574 51.7386C78.6854 52.4669 77.8903 53.3425 76.8864 54.1141C74.218 56.1689 71.3265 56.6399 68.0603 55.5504L56.4255 45.086L53.002 48.9729L53.6284 49.5365C52.7418 51.5565 49.9875 56.8422 44.9939 58.16L46.2923 63.1943C52.3843 61.5875 55.945 56.377 57.6268 53.1344L65.02 59.7813C65.2603 59.9981 65.5405 60.1657 65.8409 60.2784C68.6037 61.3072 71.069 61.4921 73.2198 61.1887C72.9309 62.2667 72.7851 63.4082 72.8938 64.5584C73.1054 66.7634 74.1979 68.6361 76.1456 70.1244C77.6529 71.2775 79.2174 71.737 80.7418 71.737C82.0431 71.737 83.3158 71.3989 84.5028 70.8729C83.5075 73.3033 83.2443 76.5776 86.0386 79.2913C87.4972 80.7074 89.1418 81.4212 90.9322 81.4212C91.0723 81.4212 91.2125 81.4154 91.3526 81.4096C95.6341 81.1466 99.1091 76.8984 99.2549 76.7192L97.7048 75.4419C97.6733 75.4795 94.6474 79.1815 91.2325 79.3924C89.8396 79.4791 88.5955 78.9676 87.4257 77.8348C85.9328 76.384 85.4695 74.6096 86.05 72.5548C86.5191 70.9018 87.4972 69.6736 87.5001 69.6707L97.6648 57.4144L96.1289 56.114L88.5068 65.3069L88.4868 65.2895C88.4725 65.3098 86.9138 67.1911 84.703 68.4974C81.9487 70.1273 79.4748 70.1331 77.3497 68.5089C75.8482 67.3616 75.0417 66.0062 74.8815 64.3676C74.7614 63.1105 75.0417 61.8303 75.485 60.6627C77.3755 60.0501 78.9485 59.0877 80.1812 58.1138C83.2386 55.6949 84.8145 52.7067 84.8803 52.5825C85.4151 51.5536 85.212 50.2879 84.3769 49.4873C84.354 49.4642 83.905 49.0336 83.1585 48.314L86.8737 42.7856L85.2178 41.6498L81.6913 46.898C79.0486 44.349 74.9072 40.3436 71.3379 36.8467L72.9367 34.3816L71.2635 33.2719L69.8764 35.4104C68.5036 34.0608 67.2737 32.8384 66.3271 31.8847C62.2343 27.7492 57.6325 27.5122 54.904 27.8561L59.6889 21.0041C59.7089 20.9781 59.7175 20.9636 59.7346 20.9347C59.7461 20.9174 60.8386 19.2817 62.6748 18.2384C64.8942 16.9755 67.208 17.1287 69.7534 18.7066C72.5019 20.4117 72.3761 26.5528 72.1158 28.5642C71.9642 29.7173 72.5877 30.8241 73.6431 31.2894C74.6985 31.7547 75.934 31.4483 76.6633 30.5525C77.0923 30.0236 80.9906 25.4748 85.2635 28.4775C88.9845 31.0929 87.1683 35.3382 86.9567 35.7977C86.4762 36.8005 86.6735 38.0056 87.4629 38.7888C88.2494 39.572 89.4364 39.7627 90.4202 39.2598C90.6605 39.1414 96.3491 36.4017 100.382 40.9187C103.677 44.6091 100.382 50.9497 100.35 51.0046C99.97 51.7126 99.9356 52.5565 100.256 53.2963C100.576 54.0332 101.217 54.5794 101.992 54.773C104.623 55.4348 106.388 56.6226 107.237 58.3045C108.733 61.2667 107.232 65.1046 107.22 65.1306C107.152 65.2982 100.227 82.0916 93.7379 93.0243C87.9663 102.74 78.3136 102.68 77.9046 102.671L77.773 107.87C77.8016 107.87 77.8617 107.87 77.9503 107.87C78.6368 107.87 81.0621 107.795 84.1595 106.85V131.258H89.3077V104.604C92.3965 102.804 95.5855 100.018 98.1481 95.6975C104.838 84.4325 111.685 67.824 111.971 67.1218C112.071 66.879 114.393 61.0962 111.842 55.981H111.851Z" fill="black"/>
          <path d="M57.6299 65.5265L56.7891 67.3616C56.9092 67.4165 68.6812 73.0721 69.6107 84.1753C70.4745 94.4837 67.0052 99.6336 66.9737 99.6798L68.6068 100.85C68.767 100.622 72.5366 95.1137 71.607 84.0019C70.5774 71.7138 58.1619 65.7692 57.6328 65.5236L57.6299 65.5265Z" fill="black"/>
        </svg>
      </div>
      <div class="sticky-card-title">
        <p class="sticky-card-title-item">SIGNEZ</p>
        <p class="sticky-card-title-item">LA PÉTITION</p>
      </div>
      <div class="punchline-wrapper">
        <p class="punchline"><?php echo esc_html( $punchline ); ?></p>
        <p class="recipient"><?php echo esc_html( $recipient ); ?></p>
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
