<?php

/**
 * Title: Legs Form Pattern
 * Description: Legs form
 * Slug: amnesty/legs-form
 * Inserter: no
 */

$image_url      = get_template_directory_uri() . '/assets/images/testator-relations-officers.png';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';

$civility      = $lastName = $firstName = $address = $zipCode = $city = $email = $phone = '';
$receiveByMail = $receiveByEmail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $civility       = isset($_POST['civility']) ? htmlspecialchars($_POST['civility']) : '';
    $lastName       = isset($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name'])) : '';
    $firstName      = isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name'])) : '';
    $address        = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
    $zipCode        = isset($_POST['zip_code']) ? htmlspecialchars(trim($_POST['zip_code'])) : '';
    $city           = isset($_POST['city']) ? htmlspecialchars(trim($_POST['city'])) : '';
    $email          = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $phone          = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $receiveByMail  = isset($_POST['receive_by_mail']);
    $receiveByEmail = isset($_POST['receive_by_email']);
}

?>

<div class="page-legs-form" id="legs-form">
	<div class="page-legs-form-wrapper">
		<div class="form-container">
			<div class="officers">
				<div class="officers-image-container">
					<img class="officers-image" src="<?php echo esc_url($image_url); ?>" alt=""/>
				</div>
				<p class="officers-names">Sophie ROUPPERT et Lisa LACOSTE</p>
				<p class="officers-job">Chargées de relations testateurs</p>
				<div class="phone-container">
					<div class="icon-container">
						<img src="<?php echo esc_url($icon_phone_url); ?>" alt=""/>
					</div>
					<p class="phone">01 53 38 66 24</p>
				</div>
			</div>
			<div class="legs-form">
				<h2 class="title">DEMANDE DE BROCHURE</h2>
				<p class="subtitle">Je souhaite recevoir la brochure d'informations sur les legs, donations et
					assurances-vie gratuitement et sans engagement :</p>
				<form class="form" id="legsForm" action="" method="POST">
					<div id="formMessages"></div>

					<div class="form-group civility">
						<label class="civility-label">Civilité :</label>
						<div class="civilities">
							<input type="radio" id="civility_m" name="civility"
									value="M." <?php echo ($civility === 'M.') ? 'checked' : ''; ?>>
							<label for="civility_m">M.</label>
							<input type="radio" id="civility_mme" name="civility"
									value="Mme" <?php echo ($civility === 'Mme') ? 'checked' : ''; ?>>
							<label for="civility_mme">Mme</label>
							<input type="radio" id="civility_other" name="civility"
									value="Autre" <?php echo ($civility === 'Autre') ? 'checked' : ''; ?>>
							<label for="civility_other">Autre</label>
						</div>
						<div id="error-civility" class="error-message-container"></div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<input type="text" id="last_name" name="last_name"
									value="<?php echo esc_attr($lastName); ?>" placeholder="Nom *">
							<div id="error-last_name" class="error-message-container"></div>
						</div>

						<div class="form-group">
							<input type="text" id="first_name" name="first_name"
									value="<?php echo esc_attr($firstName); ?>" placeholder="Prénom *">
							<div id="error-first_name" class="error-message-container"></div>
						</div>
					</div>

					<div class="form-group">
						<input type="text" id="address" name="address" value="<?php echo esc_attr($address); ?>"
								placeholder="Adresse *">
						<div id="error-address" class="error-message-container"></div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<input type="text" id="zip_code" name="zip_code" value="<?php echo esc_attr($zipCode); ?>"
									placeholder="Code Postal *" pattern="\d{5}"
									title="Veuillez entrer un code postal de 5 chiffres.">
							<div id="error-zip_code" class="error-message-container"></div>
						</div>

						<div class="form-group">
							<input type="text" id="city" name="city" value="<?php echo esc_attr($city); ?>"
									placeholder="Ville *">
							<div id="error-city" class="error-message-container"></div>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>"
									placeholder="Email *">
							<div id="error-email" class="error-message-container"></div>
						</div>

						<div class="form-group">
							<input type="tel" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>"
									placeholder="Téléphone">
							<div id="error-phone" class="error-message-container"></div>
						</div>
					</div>

					<div class="form-group">
						<div class="receive-by">
							<input type="checkbox" id="receive_by_mail"
									name="receive_by_mail" <?php echo($receiveByMail ? 'checked' : ''); ?>>
							<label for="receive_by_mail">Par courrier postal</label>
							<input type="checkbox" id="receive_by_email"
									name="receive_by_email" <?php echo($receiveByEmail ? 'checked' : ''); ?>>
							<label for="receive_by_email">Par email</label>
						</div>
						<div id="error-receive_options" class="error-message-container"></div>
					</div>

					<button class='custom-button-block left' type="submit">
						<div class="custom-button">
							<div class='content outline-black medium'>
								<div class="icon-container">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										fill="none"
										viewBox="0 0 24 24"
										strokeWidth="1.5"
										stroke="currentColor"
									>
										<path strokeLinecap="round" strokeLinejoin="round"
												d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
									</svg>
								</div>
								<div class="button-label">Envoyer</div>
							</div>
						</div>
					</button>
				</form>
			</div>
		</div>
		<p class="legals">Les informations que vous nous transmettez sont traitées par l’association Amnesty
			International France (AIF), responsable du traitement, pour répondre à vos demandes et suivre au mieux votre
			projet de transmission, pour vous communiquer des informations en lien avec notre mission et vous envoyer
			nos propositions d’engagement, qu’elles soient militantes ou financières.Conformément au Règlement européen
			général sur la protection des données du 27 avril 2016 et à la loi Informatique et Libertés modifiée, vous
			disposez d’un droit d’accès, de rectification, d’effacement, de limitation et d’opposition au traitement des
			données vous concernant, ainsi qu’un droit à la portabilité. Vous pouvez exercer ces droits en contactant le
			service relations membres et donateurs d’AIF à l’adresse mentionnée au recto, par email (smd@amnesty.fr) ou
			par téléphone (01 53 38 65 80). Vous pouvez également introduire une réclamation auprès de la CNIL. Pour
			plus d’informations sur le traitement de vos données personnelles, veuillez consulter notre politique de
			confidentialité www.amnesty.fr/politique-de-confidentialite.</p>
	</div>
</div>
