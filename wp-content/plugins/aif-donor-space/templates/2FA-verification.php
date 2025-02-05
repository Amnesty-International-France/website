<?php

/* Template Name: Espace Donateur - 2FA Check */
get_header();

$success_message = "";
$disable_input = false;
$error_message = "";
$send_code_error_message = "";
$email = "";

if (!isset($_GET['user'])) {
    $error_title = "Une erreur est survenue";
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";
} else {
    $email = $_GET['user'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['2FA-code']) && isset($_POST['2FA_nonce'])) {

    $code = sanitize_text_field($_POST['2FA-code']) ;

    if (!isset($_POST['2FA_nonce']) || !wp_verify_nonce($_POST['2FA_nonce'], '2FA_check')) {
        die('Invalid nonce.');
    }


    $user = get_user_by('email', $email);
    if ($user) {
        $stored_code = get_2fa_code($user->ID);
        if ($stored_code &&  $stored_code === $code) {
            store_email_is_verified($user->ID);
            reset_login_attempts($user->ID);

            $verification_url = add_query_arg([
                "user" => $email,
            ], get_permalink(get_page_by_path('espace-don/connectez-vous')));
            wp_redirect($verification_url);
            exit;
        } else {

            if (limit_login_attempts($user->ID)) {
                $error_message = "Le code fourni est incorrect. Veuillez réessayer";
            } else {
                $error_message = "Une erreur est survenue. Veuillez réessayer plus tard.";

            }
        }

    } else {
        $error_message = "Utilisateur inconnu ou code invalide";

    }
}


$send_code_error_message = "";
$send_code_success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['2FA_new_code_nonce'])) {


    if (!isset($_POST['2FA_new_code_nonce']) || !wp_verify_nonce($_POST['2FA_new_code_nonce'], '2FA_send_code')) {
        die('Invalid nonce.');
    }

    $sf_user = get_salesforce_member_data($email);

    if($sf_user && has_access_to_donation_space($sf_user)) {

        $stored_user = get_user_by('email', $email);

        if(!$stored_user) {

            $url = get_permalink(get_page_by_path('espace-don/creer-votre-compte'));
            $send_code_error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur la page <a class='aif-link--primary' href='{$url}'>Créer mon compte </a>.";
        } else {

            $code = generate_2fa_code();
            store_2fa_code($stored_user->ID, $code);

            $verification_url = add_query_arg([
                "user" => $email
            ], get_permalink(get_page_by_path('espace-don/verifier-votre-email')));

            $message = 'Votre nouveau code est '. $code . '. Rendez-vous sur cette url vour activer votre compte: ' . ' '. $verification_url;


            if(send_2fa_code($email, $message)) {
                $send_code_success_message = "Nous avons bien reçu votre demande de code. Il arrivera dans votre boîte email d'ici quelques minutes";
            }

        }

    } else {
        $send_code_error_message = "Utilisateur inconnu";

    }


}


?>


<main class="aif-container--main">

    <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace Don</h1>

    </header>
    <div class="aif-container--form">
        <section>
            <h2> Validation de la création de mon compte </h2>


            <p>
                Pour finaliser la création de votre compte, veuillez rentrer le code à 6 chiffres que vous venez de
                recevoir par email
            </p>

            <form role="form" method="POST" action="">
                <?php wp_nonce_field('2FA_check', '2FA_nonce'); ?>
                <label for="2FA-code">Code à 6
                    chiffres (obligatoire)</label>
                <input class="aif-input" pattern="\d{6}" title="rentrer ici votre code de 6 chiffres reçu par email"
                    placeholder="" id=" 2FA-code" value="" type="text" name="2FA-code" required="true">

                <?php if (!empty($error_message)) : ?>
                <?php
            $title = "Une erreur est survenue";
                    aif_include_partial("alert", [
                        "title" => $title,
                        "state" => "error",
                    "content" => $error_message])

                    ?>

                <?php endif; ?>

                <button class="btn aif-mt1w aif-button--full" type="submit">Valider la création de mon compte</button>
            </form>


        </section>

        <section class="aif-mb1w aif-mt1w">

            <h2> Code non reçu ou invalide ? </h2>

            <?php if (!empty($send_code_error_message)) {
                $title = "Une erreur est survenue";
                aif_include_partial("alert", [
                    "state" => "error",
                    "title" => $title,
                "content" => $send_code_error_message]);

            }?>

            <?php
             if (!empty($send_code_success_message)) {
                 aif_include_partial(
                     "alert",
                     [
                     "title" => "Information",
                     "content" => $send_code_success_message,
                      "success" => true
                      ]
                 );
             }?>

            <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('2FA_send_code', '2FA_new_code_nonce'); ?>

                <button class="btn aif-mt1w aif-button--full" type="submit">Recevoir un nouveau code</button>
            </form>
            
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