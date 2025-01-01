<?php

/* Template Name: Create account */
get_header();

$error_all_fields_required_message = "";
$error_invalid_email_message  = "";
$error_password_not_match_message  = "";
$error_technical_message  = "";
$error_no_access_to_donor_space = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $confirm_password = sanitize_text_field($_POST['confirm-password']);

    if (empty($email) || empty($password)) {
        $error_all_fields_required_message = "Veuillez renseigner le mot de passe et votre email";
    } elseif (!is_email($email)) {
        $error_invalid_email_message = "L'email renseigné est invalide";

    } elseif ($password !== $confirm_password) {
        $error_password_not_match_message  = "Les mots de passe ne correspondent pas";
    } else {

        $sf_member = get_salesforce_member_data($email);

        if(has_access_to_donation_space($sf_member)) {

            $user = get_salesforce_user_data($sf_member->Id);

            $userdata = array(
                'user_login'    => $email,
                'user_email'    => $email,
                'user_pass'     => $password,
                'first_name'    => $user->Name,
                'last_name'     => $user->LastName,
                'nickname'      => $user->FirstName . ' ' . $user->LastName,
                'role'          => 'subscriber');

            $user_id = wp_insert_user($userdata);

            if (!is_wp_error($user_id)) {
                store_SF_user_ID($user_id, $sf_member->Id);

                $code = generate_2fa_code();
                store_2fa_code($user_id, $code);

                $verifaction_url = get_permalink(get_page_by_path('verifier-votre-email'));

                if(send_2fa_code($email, $code, $verifaction_url)) {
                    wp_redirect($verifaction_url);
                    exit;
                }
            } else {
                $url = get_permalink(get_page_by_path('connectez-vous'));
                $error_technical_message = "Vous vous êtes déja inscrit. Pour vous rendre sur votre Espace Don rendez-vous sur  <a class='aif-text-underline 
    aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
            }


        } else {
            $error_no_access_to_donor_space = true;
        }
    }

}



?>

<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">


    <div class="container">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>
            <h2> Créer mon compte </h2>
        </header>

        <p class="aif-mb0">Votre espace don vous permet de suivre facilement vos dons et adhésion. Vous pouvez y
            modifier vos
            coordonnées personnelles, votre RIB et éditer des duplicatas de vos reçus fiscaux. </p>

        <p class="aif-mb1w"> Commencez par renseigner l’adresse e-mail utilisé lors de la réalisation de votre
            adhésion ou de
            votre don.
        </p>

        <?php if (!empty($error_all_fields_required_message) || !empty($error_invalid_email_message) || !empty($error_password_not_match_message) || !empty($error_technical_message)) : ?>
        <div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

            <div class="aif-flex aif-gap-single">
                <div>
                    <svg aria-hidden="true" width="4" height="14" viewBox="0 0 4 14" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                            fill="#D51118" />
                    </svg>
                </div>
                <div>
                    <p class="aif-text-red aif-text-bold">Une erreur est survenue</p>

                    <p class="aif-mb0">

                        <?php echo $error_all_fields_required_message  ?>
                        <?php echo $error_invalid_email_message  ?>
                        <?php echo $error_password_not_match_message  ?>
                        <?php echo $error_technical_message  ?>

                    </p>

                </div>
            </div>
        </div>

        <?php endif; ?>

        <?php if ($error_no_access_to_donor_space) : ?>
        <div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

            <div class="aif-flex aif-gap-single">
                <div>
                    <svg aria-hidden="true" width="4" height="14" viewBox="0 0 4 14" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                            fill="#D51118" />
                    </svg>
                </div>
                <div>
                    <p class="aif-text-red aif-text-bold">L’adresse email renseignée ne trouve pas de
                        correspondance
                        dans notre
                        système.</p>

                    <p class="aif-mb0">

                        Devenez donateur en <a>réalisant un don </a> ou <a class="aif-text-underline 
aif-text-underline--orange" href="mailto:smd@amnesty.fr"> contatcer le
                            Service membres et
                            donateurs </a> si vous pensez
                        que c’est une erreur.

                    </p>

                </div>
            </div>


        </div>
        <?php endif; ?>

        <form action="" method="POST" onsubmit="return checkPasswordMatch()">

            <label for="email">Adresse email (obligatoire) :</label>
            <input type="email" id="email" name="email" placeholder="Votre adresse email" autocomplete="email" required>

            <label for="password">Mot de passe (obligatoire) :</label>
            <input type="password" id="password" name="password" required aria-required="true"
                placeholder="Votre mot de passe" aria-describedby="passwordHelp passphraseRequirements"
                autocomplete="new-password" required aria-required="true" oninput="checkPassphraseStrength()">
            <small id=" passwordHelp">
                Exemple de mot de passe valide : <strong>Mon@MotDePasse123</strong> (au moins 6 caractères, une
                majuscule, un chiffre et un caractère spécial)
            </small>

            <div id="password-error-too-weak" class="invalid password-error-message">
                Le mot de passe est trop faible
            </div>

            <div id="passphraseRequirements">
                <p class=" aif-m0 aif-mt1w ">Votre mot de passe doit </p>
                <ul>
                    <li id=" length">Doit contenir au moins 6 caractères</li>
                    <li id="uppercase">Doit contenir au moins une lettre majuscule</li>
                    <li id="lowercase">Doit contenir au moins une lettre minuscule</li>
                    <li id="number">Doit contenir au moins un chiffre</li>
                    <li id="special">Doit contenir au moins un caractère spécial (!, @, #, $, %,
                        etc.)
                    </li>
                </ul>
            </div>

            <label for="confirm-password">Confirmer le mot de passe (obligatoire) :</label>
            <input type="password" id="confirm-password" required aria-required="true" name="confirm-password"
                placeholder="Confirmer votre mot de passe" autocomplete="new-password" required
                oninput="checkPasswordMatch()">

            <div id="password-error-not-match" class="invalid password-error-message">Les mots de passe ne
                correspondent
                pas.
            </div>

            <button class="btn aif-mt1w" type="submit" id="submit-btn">Créer mon compte</button>

        </form>

        <hr class="aif-mt1w">

        <p class="aif-mt1w">
            Les données personnelles collectées sur ce formulaire sont traitées par l’association Amnesty
            International
            France (AIF), responsable du traitement. Ces données vont nous permettre de vous envoyer nos
            propositions
            d’engagement, qu’elles soient militantes ou financières. Notre politique de confidentialité détaille la
            manière dont Amnesty International France, en sa qualité de responsable de traitement, traite et protège
            vos
            données personnelles collectées conformément aux dispositions de la Loi du 6 janvier 1978 relative à
            l’informatique, aux fichiers et aux libertés dite Loi « Informatique et Libertés », et au Règlement
            européen
            du 25 mai 2018 sur la protection des données (« RGPD »). Pour toute demande, vous pouvez contacter le
            service membres et donateurs d’AIF à l’adresse mentionnée ci-dessus, par email smd@amnesty.fr. Vous
            pouvez
            également introduire une réclamation auprès de la CNIL. Pour plus d’information sur le traitement de vos
            données personnelles, veuillez consulter notre politique de confidentialité.
        </p>


        <section class="aif-bg-primary aif-p1w aif-mt2w">

            <h3> Vous n’êtes pas encore donateur ? </h3>

            <p>
                Votre soutien servira à soutenir le travail d’enquête ainsi que l'ensemble des actions d'Amnesty
                International.
            </p>

            <a href="" class="btn btn--dark"> Soutenez-nous !</a>

        </section>

        <section class="aif-mt2w aif-mb1w">

            <h3> Vous avez déja un compte ? </h3>

            <a href="<?php echo get_permalink(get_page_by_path('connectez-vous')) ?>" class="btn btn--dark">
                Connectez-vous ! !</a>


        </section>


    </div>

</main>

<?php
get_footer()
?>