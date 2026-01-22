<?php

/**
 * Title: Foundation Form Pattern
 * Description: foundation form
 * Slug: amnesty/foundation-form
 * Inserter: no
 */

declare(strict_types=1);


$image_url      = get_template_directory_uri() . '/assets/images/amnesty-foundation.jpg';
$icon_phone_url = get_template_directory_uri() . '/assets/images/icon-phone.svg';

$civility      = $lastName = $firstName = $address = $zipCode = $city = $email = $phone = '';
$receiveByPostalMail = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $civility       = isset($_POST['civility']) ? htmlspecialchars($_POST['civility']) : '';
    $lastName       = isset($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name'])) : '';
    $firstName      = isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name'])) : '';
    $email          = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $phone          = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $message 		= isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $receiveByPostalMail  = isset($_POST['receive_by_postal_mail']);
}

?>

<div class="page-foundation-form">
	<div class="page-foundation-form-wrapper">
		<div class="form-container">
			<div class="officers">
				<div class="officers-image-container">
					<img class="officers-image" src="<?php echo esc_url($image_url); ?>" alt=""/>
				</div>
				<p class="officers-job">Chargée de la relation avec les donatrices et donateurs de la Fondation</p>
				<p class="officers-names">Milena Djelic</p>
				<div class="phone-container">
					<div class="icon-container">
						<img src="<?php echo esc_url($icon_phone_url); ?>" alt=""/>
					</div>
					<p class="phone">01 53 38 65 31</p>
				</div>
			</div>
			<div class="foundation-form">
				<h2 class="title">À VOTRE ÉCOUTE</h2>
				<p class="officers-citation">Je suis à votre écoute pour toute question ou pour tout besoin d’information à propos de la Fondation, ou des dispositions fiscales qui accompagnent votre générosité.</p>
				<form class="form" id="foundationForm" action="" method="POST" data-gtm-type="fondation" data-gtm-name="fondation">
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
							<label for="last_name"></label>
							<input type="text" id="last_name" name="last_name"
									value="<?php echo esc_attr($lastName); ?>" placeholder="Nom *">
							<div id="error-last_name" class="error-message-container"></div>
						</div>

						<div class="form-group">
							<label for="first_name"></label>
							<input type="text" id="first_name" name="first_name"
									value="<?php echo esc_attr($firstName); ?>" placeholder="Prénom *">
							<div id="error-first_name" class="error-message-container"></div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="email"></label>
							<input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>"
									placeholder="Email *">
							<div id="error-email" class="error-message-container"></div>
						</div>

						<div class="form-group">
							<label for="phone"></label>
							<input type="tel" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>"
									placeholder="Téléphone">
							<div id="error-phone" class="error-message-container"></div>
						</div>
					</div>
					<div class="form-group">
						<label for="message"></label>
						<textarea name="message" id="message" cols="30" rows="5" placeholder="Un message à nous laisser ?"></textarea>

						<div id="error-phone" class="error-message-container"></div>
					</div>
					<div class="form-group">
						<div class="receive-by">
							<input type="checkbox" id="receive_by_mail"
									name="receive_by_mail" <?php echo($receiveByPostalMail ? 'checked' : ''); ?>>
							<label for="receive_by_mail">Je souhaite recevoir des informations sur la Fondation Amnesty International France par courrier postal.</label>
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
		<p class="legals">Les informations recueillies sur ce formulaire sont enregistrées dans un fichier informatisé et sécurisé par Amnesty International France (AIF),
			à des fins de traitement administratif de votre don et de votre reçu fiscal, pour répondre à vos demandes,
			pour vous communiquer des informations en lien avec notre mission ou faire appel à votre engagement.
			Le responsable de traitement est AIF, Association Loi 1901, dont le siège social est situé au 76 bd de la Villette, 75940 Paris cedex 19.
			AIF est représentée par Anne Savinel-Barras, sa Présidente. Elles sont destinées au secrétariat administratif de la Fondation AIF et aux tiers mandatés par celle-ci.
			Vos données personnelles sont hébergées sur des serveurs informatiques situés en Europe et aux États-Unis.
			Des règles assurant la protection et la sécurité de ces données ont été mises en place.
			Elles sont disponibles sur simple demande adressée à la Fondation.
			Ces informations sont conservées pendant la durée strictement nécessaire à la réalisation des finalités précitées.
			Conformément à la loi « informatique et libertés » et à la réglementation européenne,
			vous disposez d’un droit d’accès, de rectification, de suppression, de restriction et d’opposition au traitement des données vous concernant, ainsi qu’un droit à la portabilité en contactant :
			Fondation Amnesty International France – Secrétariat administratif – 76 bd de la Villette CS 40088 75939 Paris Cedex 19 – 01 53 38 65 65 – fondation@amnesty.fr. Vous pouvez également introduire une réclamation auprès de la CNIL.</p>
	</div>
</div>
