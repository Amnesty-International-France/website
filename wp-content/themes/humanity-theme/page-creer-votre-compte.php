<?php

$error_message = '';
$error_no_access_to_donor_space = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_turnstile()) {
        die('Turnstile verification failed.');
    }

    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $confirm_password = sanitize_text_field($_POST['confirm-password']);

    if (empty($email) || empty($password)) {
        $error_message = 'Veuillez renseigner le mot de passe et votre email';
    } elseif (!is_email($email)) {
        $error_message = "L'email renseigné est invalide";

    } elseif ($password !== $confirm_password) {
        $error_message  = 'Les mots de passe ne correspondent pas';
    } else {

        $sf_member = get_salesforce_member_data($email);

        if (has_access_to_donation_space($sf_member)) {
            $user = get_salesforce_user_data($sf_member->Id);

            $userdata = [
                'user_login'    => $email,
                'user_email'    => $email,
                'user_pass'     => $password,
                'first_name'    => $user->FirstName,
                'last_name'     => $user->LastName,
                'nickname'      => $user->FirstName . ' ' . $user->LastName,
                'role'          => 'subscriber'];

            $user_id = wp_insert_user($userdata);

            if (!is_wp_error($user_id)) {
                $code = generate_2fa_code();
                store_2fa_code($user_id, $code);

                $verification_url = add_query_arg([
                    'user' => $email,
                ], get_permalink(get_page_by_path('verifier-votre-email')));

                if (send_2fa_code($email, $code)) {
                    wp_redirect($verification_url);
                    exit;
                }
            } else {
                $url = get_permalink(get_page_by_path('connectez-vous'));
                $error_message = "Vous semblez déjà avoir un compte espace don. Pour vous rendre sur votre Espace Don rendez-vous sur  <a class='aif-link--primary' href='{$url}'>{$url}</a>.";
            }

        } else {
            $error_no_access_to_donor_space = true;
        }
    }

}

?>

<?php get_header(); ?>

<main class="aif-container--main">
    <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace Don</h1>
    </header>

    <div class="aif-container--form">
        <h2> Je crée mon compte </h2>
        <p class="">Votre espace don vous permet de suivre facilement vos dons et adhésion. Vous pouvez y
            modifier vos coordonnées personnelles, votre RIB et éditer des duplicatas de vos reçus fiscaux.</p>
        <?php
        if (!empty($error_message)) {
            $title = 'Une erreur est survenue';
            aif_include_partial('alert', [
                'state' => 'error',
                'title' => $title,
                'content' => $error_message]);
        }

if (!empty($error_no_access_to_donor_space)) {
    $title = 'L’adresse email renseignée ne trouve pas de correspondance dans notre système.';
    $content = 'Devenez donateur en <a href="#" class="aif-link--secondary">réalisant un don </a> ou <a
                                                    class="aif-link--secondary" href="mailto:smd@amnesty.fr">contactez le Service
                                                    membres et donateurs</a> si vous pensez que c’est une erreur.';

    aif_include_partial('alert', [
        'state' => 'error',
        'title' => $title,
        'content' => $content]);
}
?>

        <section>
            <h3 class="aif-sr-only">S'inscrire</h3>
            <form class="aif-form-container" action="" method="POST" onsubmit="return checkPasswordMatch()">
                <div>
					<div class="cf-turnstile" data-sitekey="<?php echo esc_attr(getenv('TURNSTILE_SITE_KEY')); ?>"></div>
                    <label for="email">Adresse email (obligatoire) :</label>
                    <input type="email" class="aif-input" id="email" name="email" aria-describedby="email-help-message"
                        placeholder="adresse@mail.fr" autocomplete="email" aria-required="true" required>
                    <?php
            aif_include_partial('info-message', [
                'id' => 'email-help-message',
                'content' => 'Commencez par renseigner l’adresse e-mail utilisé lors de la réalisation de votre adhésion ou de votre don.']);
?>
                </div>
                <div class="aif-password-container">
                    <label class="aif-password-container__label" for="password">Votre mot de passe (obligatoire)</label>
                    <div class="aif-password-container__input-wrapper">
                        <input class="aif-password-container__input aif-input" placeholder="Mot de passe"
                            name="password" aria-describedby="passwordHelp passphraseRequirements" type="password"
                            id="password" autocomplete="new-password" required aria-required="true"
                            oninput="checkPassphraseStrength(); checkPasswordMatch();">
                        <button class="aif-password-container__button" type="button" id="toggle-password"
                            data-target="password" aria-label="Afficher ou masquer le mot de passe">
                            Afficher
                        </button>
                    </div>

                    <div id="password-error-too-weak" class="aif-text-red aif-hide">
                        Le mot de passe est trop faible
                    </div>

                    <?php
    aif_include_partial('info-message', [
        'id' => 'passwordHelp',
        'content' => 'Exemple : Mon@MotDePasse123']);
?>

                    <div id="passphraseRequirements aif-text-small">
                        <p class=" aif-m0 aif-text-small">Votre mot de passe : </p>
                        <ul class="aif-text-small">
                            <li id=" length">Doit contenir au moins 6 caractères</li>
                            <li id="uppercase">Doit contenir au moins une lettre majuscule</li>
                            <li id="lowercase">Doit contenir au moins une lettre minuscule</li>
                            <li id="number">Doit contenir au moins un chiffre</li>
                            <li id="special">Doit contenir au moins un caractère spécial (!, @, #, $, %,
                                etc.)
                            </li>
                        </ul>
                    </div>

                    <div class="aif-password-container">
                        <label class="aif-password-container__label" for="confirm-password">Confirmer votre mot de passe
                            (obligatoire)</label>
                        <div class="aif-password-container__input-wrapper">
                            <input class="aif-password-container__input aif-input" name="confirm-password"
                                type="password" id="confirm-password" autocomplete="new-password" required
                                aria-required="true" placeholder="Mot de passe" oninput="checkPasswordMatch()">
                            <button class="aif-password-container__button" type="button" id="toggle-confirm-password"
                                data-target="confirm-password" aria-label="Afficher ou masquer le mot de passe">
                                Afficher
                            </button>
                        </div>

                        <div id="password-error-not-match" class="aif-text-red aif-hide">Les mots de passe ne
                            correspondent
                            pas.
                        </div>
                        <button class="btn  aif-mt1w aif-button--full" type="submit" id="submit-btn">Créer mon compte</button>
                    </div>
                </div>
            </form>

            <p class="aif-mt1w aif-text-small">
                Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty
                International France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer
                nos propositions d’engagement, qu’elles soient militantes ou financières. Notre politique de
                confidentialité détaille la manière dont Amnesty International France, en sa qualité de responsable de
                traitement, traite et protège vos données personnelles collectées conformément aux dispositions de la
                Loi du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés dite Loi « Informatique et
                Libertés », et au Règlement européen du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute
                demande, vous pouvez contacter le service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus,
                par email smd@amnesty.fr. Vous pouvez également introduire une réclamation auprès de la CNIL. Pour plus
                d’information sur le traitement de vos données personnelles, veuillez consulter notre politique de
                confidentialité.
            </p>
        </section>

        <section class="aif-bg-primary aif-p1w aif-mt1w">
            <h3> Vous n’êtes pas encore donateur ? </h3>
            <p>Votre soutien servira à soutenir le travail d’enquête ainsi que l'ensemble des actions d'Amnesty International.</p>
            <a href="" class="btn btn--dark aif-button--full"> Nous soutenir</a>
        </section>

        <section class="aif-mt2w aif-mb1w">
            <h3> Vous avez déja un compte ? </h3>
            <a href="<?php echo get_permalink(get_page_by_path('connectez-vous')) ?>" class="btn  btn--white aif-button--full ">Se connecter</a>
        </section>
    </div>
</main>

<?php get_footer(); ?>
