<?php

/* Template Name: Espace Donateur - Login */
get_header();

$email = "";



if (!isset($_GET['user'])) {
    $error_title = "Une erreur est survenue";
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";

} else {
    $user = get_user_by("email", $_GET['user']);
    if ($user) {
        $email = $user->user_email;
    }

}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['login_nonce'])) {

    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']) ;

    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'login_form')) {
        die('Invalid nonce.');
    }
    $stored_user = get_user_by('email', $email);


    if ($stored_user) {
        if (get_email_is_verified(user_id: $stored_user->ID)) {

            $creds = array(
                'user_login'    => $email,
                'user_password' => $password,
                'remember'      => true
            );

            $user = wp_signon($creds, true);

            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);

                $verification_url = get_permalink(get_page_by_path('espace-don'));
                wp_redirect($verification_url);
                exit;
            } else {
                $error_message = "Mauvais email ou mot de passe";
            }


        } else {
            $verification_url = add_query_arg([
                "user" => $email,
            ], get_permalink(get_page_by_path('espace-don/verifier-votre-email')));
            $error_message = "Votre email ne semble pas encore vérifié. Pour le faire veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='{$verification_url}'> {$verification_url}</a>.";
        }
    } else {
        $url = get_permalink(get_page_by_path('espace-don/creer-votre-compte'));
        $error_message = "Votre compte n'existe pas. Pour le créer, veuillez vous rendre sur  <a class='aif-text-underline 
aif-text-underline--orange' href='" . $url . "'>" . $url . "</a>.";
    }

}
?>




<main class="aif-container--main">

        <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
            <h1 class="aif-mb1w">Mon espace Don</h1>
     
        </header>
<div class="aif-container--form">
<h2> Je me connecte </h2>
        <?php
        if (!empty($error_message)) {
            $title = "Une erreur est survenue";

            aif_include_partial("alert", [
                "title" => $title,
            "content" => $error_message]);

        }

?>

        <section>
            <h3 class="aif-sr-only"> Formulaire de connexion </h3>
            <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                <label for="email">Votre adresse email (obligatoire)</label>
                <input placeholder=""
                    value="<?= $email ? $email : '' ?>"
                     class="aif-input"
                    type="email" name="email" id="email" autocomplete="email" required="true">
                <div class="aif-password-container">
        <label class="aif-password-container__label" for="password">Votre mot de passe (obligatoire)</label>
        <div class="aif-password-container__input-wrapper">
            <input class="aif-password-container__input aif-input" name="password" aria-describedby="passwordHelp passphraseRequirements" type="password" id="password" autocomplete="new-password" required aria-required="true" oninput="checkPassphraseStrength()">
            <button class="aif-password-container__button" type="button" id="toggle-password"  data-target="password" aria-label="Afficher ou masquer le mot de passe">
                Afficher
            </button>
        </div>
                <button class="btn aif-mt1w aif-button--full" type="submit">Se connecter</button>

            </form>

            <a class="aif-link--primary aif-mt1w aif-block"
                href="<?=  get_permalink(get_page_by_path('espace-don/mot-de-passe-oublie')) ?>">
                Mot de passe oublié ? </a>

        </section>


        <section class="aif-mt2w aif-mb1w">
            <h3>Première connexion</h3>

            <p> La création de votre espace don n’est pas automatique lorsque vous faites un don.Si vous n’avez
                jamais
                créé votre espace, merci de cliquer sur “Créer votre compte”. </p>

            <a href="<?=  get_permalink(get_page_by_path('espace-don/creer-votre-compte')) ?>"
                class="btn aif-button--full">Créer
                votre compte</a>

        </section>


    </div>

</main>

<?php
get_footer();
?>