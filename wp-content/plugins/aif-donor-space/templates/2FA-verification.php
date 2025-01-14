<?php

/* Template Name: Espace Donateur - 2FA Check */
get_header();

$success_message = "";
$disable_input = false;
$error_message = "";
$send_code_error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['2FA-code']) && isset($_POST['2FA_nonce'])) {

    $email = sanitize_text_field($_POST['email']);
    $code = sanitize_text_field($_POST['2FA-code']) ;

    if (!isset($_POST['2FA_nonce']) || !wp_verify_nonce($_POST['2FA_nonce'], '2FA_check')) {
        die('Invalid nonce.');
    }

    $user = get_user_by('email', $email);

    if($user) {

        $stored_code = get_2fa_code($user->ID);
        if($stored_code &&  $stored_code === $code) {
            store_email_is_verified($user->ID);
            reset_login_attempts($user->ID);
            wp_redirect(get_permalink(get_page_by_path('espace-donateur/connectez-vous')));
            exit;
        } else {

            $disable_input = false;

            if(limit_login_attempts($user->ID)) {
                $error_message = "Le code ne correspond pas";

            } else {
                $error_message = "La vérification du code à échoué. Veuillez recommencer dans quelques secondes";

            }


        }

    } else {
        $error_message = "Utilisateur inconnu ou code invalide";

    }


}

$send_code_error_message = "";
$send_code_success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['2FA-code-email']) && isset($_POST['2FA_new_code_nonce'])) {


    $email = sanitize_text_field($_POST['2FA-code-email']);


    if (!isset($_POST['2FA_new_code_nonce']) || !wp_verify_nonce($_POST['2FA_new_code_nonce'], '2FA_send_code')) {
        die('Invalid nonce.');
    }

    $sf_user = get_salesforce_member_data($email);

    if($sf_user && has_access_to_donation_space($sf_user)) {

        $stored_user = get_user_by('email', $email);

        if(!$stored_user) {

            $url = get_permalink(get_page_by_path('espace-donateur/creer-votre-compte'));
            $send_code_error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur  <a class='aif-text-underline 
    aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
        } else {

            $code = generate_2fa_code();
            store_2fa_code($stored_user->ID, $code);

            $verification_url = get_permalink(get_page_by_path('espace-donateur/verifier-votre-email'));
            $message = 'Votre nouveau code est '. $code . '. Rendez-vous sur cette url vour activer votre compte: ' . ' '. $verification_url;


            if(send_2fa_code($email, $message)) {
                $send_code_success_message = "Votre code est en chemin";
            }

        }

    } else {
        $send_code_error_message = "Utilisateur inconnu";

    }


}


?>


<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">
    <div class="container">
        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>

        </header>

        <section>
            <h2> Validation de la création de mon compte </h2>


            <?php if (!empty($error_message)) : ?>
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
                            <?php echo $error_message  ?>
                        </p>

                    </div>
                </div>

            </div>

            <?php endif; ?>

            <p>Votre espace don vous permet de suivre facilement vos dons et adhésion. Vous pouvez y modifier vos
                coordonnées personnelles, votre RIB et éditer des duplicatas de vos reçus fiscaux.</p>

            <p>
                Pour finaliser ma création de compte, rentrer votre email et le code à 6 chiffres que vous venez de
                recevoir
                sur votre email.
            </p>

                <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('2FA_check', '2FA_nonce'); ?>
                <label class="<?php echo !empty($error_message) ? 'aif-input-error' : "" ?>" for="email">Votre
                    adresse email
                    (obligatoire)</label>
                <input class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" placeholder="" value=""
                    type="email" name="email" id="email" autocomplete="email" required="true">
                <label class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" for="2FA-code">Code à 6
                    chiffres (obligatoire)</label>
                <input pattern="\d{6}" title="rentrer ici votre code de 6 chiffres reçu par email"
                    class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" placeholder="" id=" 2FA-code"
                    value="" type="text" name="2FA-code" required="true">

                <button class="btn aif-mt1w" type="submit">Valider la création de mon compte</button>
            </form>





        </section>

        <section class="aif-mb1w aif-mt1w">


            <?php if (!empty($send_code_success_message)) : ?>
            <div class="aif-bg-grey--lighter aif-p1w aif-mb1w">

                <div class="aif-flex aif-gap-single">
                    <div class="aif-text-green">
                        <svg aria-hidden="true" width="4" height="14" viewBox="0 0 4 14" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M2 14C1.45 14 0.979167 13.8477 0.5875 13.5431C0.195833 13.2384 0 12.8722 0 12.4444C0 12.0167 0.195833 11.6505 0.5875 11.3458C0.979167 11.0412 1.45 10.8889 2 10.8889C2.55 10.8889 3.02083 11.0412 3.4125 11.3458C3.80417 11.6505 4 12.0167 4 12.4444C4 12.8722 3.80417 13.2384 3.4125 13.5431C3.02083 13.8477 2.55 14 2 14ZM0 9.33333V0H4V9.33333H0Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <div>
                        <p class="aif-text-green aif-text-bold">Votre code est en chemin</p>

                        <p class="aif-mb0">
                            Vous le receverez par email d'ici quelques minutes
                        </p>

                    </div>
                </div>

            </div>

            <?php endif; ?>


            <?php if (!empty($send_code_error_message)) : ?>
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
                            <?php echo $send_code_error_message  ?>
                        </p>

                    </div>
                </div>

            </div>

            <?php endif; ?>
            <h2> Code non reçu ou invalide ? </h2>

                <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('2FA_send_code', '2FA_new_code_nonce'); ?>
                <label class="<?php echo !empty($error_message) ? 'aif-input-error' : "" ?>" for="2FA-code-email">Votre
                    adresse email
                    (obligatoire)</label>
                <input class="<?php echo !empty($error_message) ? "aif-input-error" : "" ?>" placeholder="" value=""
                    type="email" name="2FA-code-email" id="2FA-code-email" autocomplete="email" required="true">


                <button class="btn aif-mt1w" type="submit">Recevoir un nouveau code</button>
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
        </section>

    </div>

</main>

<?php
get_footer()
?>