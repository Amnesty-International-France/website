<?php

$email = '';

$reset_email_url = '';

if (!isset($_GET['user'])) {
    $error_title = 'Une erreur est survenue';
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";

} else {
    $user = get_user_by('email', $_GET['user']);
    if ($user) {
        $email = $user->user_email;
    }
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']) ;

    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'login_form')) {
        die('Invalid nonce.');
    }

    $stored_user = get_user_by('email', $email);

    if ($stored_user) {
        if (get_email_is_verified(user_id: $stored_user->ID)) {

            $creds = [
                'user_login'    => $email,
                'user_password' => $password,
                'remember'      => true,
            ];

            $sf_member = get_salesforce_member_data($email);
            store_SF_user_ID($stored_user->ID, $sf_member->Id);
            $user = wp_signon($creds, true);

            if (!is_wp_error($user)) {
                wp_set_current_user($user->ID);
                $verification_url = get_permalink(get_page_by_path('mon-espace'));
                wp_redirect($verification_url);
                exit;
            } else {
                $title = 'Une erreur est survenue';
                $error_message = 'Mauvais email ou mot de passe';
            }

        } else {
            $verification_url = add_query_arg([
                'user' => $email,
            ], get_permalink(get_page_by_path('verifier-votre-email')));

            $title = 'Votre email ne semble pas encore vérifié';
            $error_message = "Pour le faire veuillez vous rendre sur la page <a class='aif-link--primary' href='{$verification_url}'> Vérifier mon email</a>.";
        }
    } else {
        $url = get_permalink(get_page_by_path('creer-votre-compte'));
        $title = "L'adresse email renseignée ne trouve pas de correspondance dans notre système";
        $error_message = "Pour créer votre compte, veuillez vous rendre sur la page  <a class='aif-link--primary' href='{$url}'> Créer mon compte </a>.";
    }

}

$image_url = get_template_directory_uri() . '/assets/images/login-background.png';
?>

<?php get_header(); ?>

<main class="aif-login-page">
    <div class="login-form-container">
        <div class="login-form-header">
            <span class="login-form-title">Mon espace</span>
            <div class="login-form-logo">
                <?php
                if (function_exists('amnesty_logo')) {
                    amnesty_logo();
                }
?>
            </div>
        </div>
        <div class="login-form">
            <h3 class="login-title">Connexion</h3>
            <form class="aif-form-container" role="form" method="POST" action="">
                <?php wp_nonce_field('login_form', 'login_nonce'); ?>
                <label for="email" class="sr-only">Votre adresse email (obligatoire)</label>
                <input placeholder="Email"
                    value="<?= $email ? $email : '' ?>" class="aif-input" type="email" name="email" id="email" autocomplete="email" required="true">
                <div class="aif-password-container">
                    <label class="aif-password-container__label sr-only" for="password">Votre mot de passe (obligatoire)</label>
                    <div class="aif-password-container__input-wrapper">
                        <input placeholder="Mot de passe" class="aif-password-container__input aif-input"
                            name="password" aria-describedby="passwordHelp passphraseRequirements" type="password"
                            id="password" autocomplete="new-password" required aria-required="true"
                            oninput="checkPassphraseStrength()">
                    </div>
                    <?php
    if (!empty($error_message)) {
        aif_include_partial('alert', [
            'state' => 'error',
            'title' => $title,
            'content' => $error_message]);

    };
?>
                </div>
                <div class="aif-flex aif-justify-end">
                    <div>
                        <a class="aif-link--primary aif-block"
                            href="<?= get_permalink(get_page_by_path('mot-de-passe-oublie')) ?>">
                            Mot de passe oublié ?
                        </a>
                        <a class="aif-link--primary aif-block"
                            href="<?= get_permalink(get_page_by_path('foire-aux-questions')) ?>">
                            Un problème de connexion ?
                        </a>
                    </div>
                </div>
                <div class="custom-button-block center">
                    <button type="submit" class="custom-button">
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
                            <div class="button-label">Se connecter</div>
                        </div>
                    </button>
                </div>
            </form>
            <div class="no-account-section">
                <span class="first-connection">Première connexion ?</span>
                <p class="description">La création de votre espace n’est pas automatique lorsque vous faites un don. Si vous n’avez jamais créé votre espace, merci de cliquer sur “Créer votre compte”.</p>
                <a href="<?=  get_permalink(get_page_by_path('creer-votre-compte')) ?>"class="aif-link--primary create-account aif-block">Créer votre compte</a>
            </div>
        </div>
        <div class="login-form-decorative-image-wrapper">
            <div class="login-form-decorative-image"></div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
