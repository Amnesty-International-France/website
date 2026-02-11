<?php

if (!isset($_GET['user']) || !isset($_GET['token'])) {
    $error_title = 'Une erreur est survenue';
    $error_message = "Nous ne pouvons récupérer l'utilisateur associé à l'identifiant.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirm-password']) && isset($_GET['user']) && isset($_GET['token'])) {
    if (!isset($_POST['reset_password_nonce']) || !wp_verify_nonce($_POST['reset_password_nonce'], 'reset_password_form')) {
        die('Invalid nonce.');
    }

    $email = $_GET['user'];
    $token = $_GET['token'];
    $password = $_POST['password'];
    $user = get_user_by('email', $email);

    if ($user) {
        $stored_token = get_email_token($user->ID);
        if ($token === $stored_token) {

            if ($password == $_POST['confirm-password']) {
                $userdata = [
                    'ID'        => $user->ID,
                    'user_pass' => $password,
                ];
                wp_update_user($userdata);

                $url = add_query_arg([
                    'user' => $user->user_email,
                ], get_permalink(get_page_by_path('connectez-vous')));

                wp_redirect($url);
                exit;

            } else {
                $error_title = 'Une erreur est survenue.';
                $error_message = 'Les mots de passe ne correspondent pas';
            }
        } else {
            $error_title = 'Une erreur est survenue';
            $error_message = 'Les informations fournies sont erronnées';
        }
    } else {
        $error_title = 'Votre utilisateur nous est inconnu';
        $error_message = 'Devenez donateur en <a>réalisant un don </a> ou <a
                          class="aif-link--primary" href="mailto:smd@amnesty.fr">contacter le Service membres et donateurs</a> si vous pensez que c’est une erreur.';
    }
}
?>

<?php get_header(); ?>

<main class="aif-container--main">
    <?php
        if (isset($error_message)) {
            $error_title = 'Une erreur est survenue.';
            aif_include_partial('alert', [
                'title' => $error_title,
                'state' => 'error',
                'content' => $error_message]);
        }
?>

    <header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
        <h1 class="aif-mb1w">Mon espace Don</h1>
    </header>
    <section class="aif-container--form">
        <h2>Modifier mon mot de passe</h2>
        <form class="aif-form-container" action="" method="POST">
            <?php wp_nonce_field('reset_password_form', 'reset_password_nonce'); ?>
            <div class="aif-password-container">
                <label class="aif-password-container__label" for="password">Nouveau mot de passe (obligatoire)</label>
                <div class="aif-password-container__input-wrapper">
                    <input class="aif-password-container__input aif-input" name="password"
                        aria-describedby="passwordHelp passphraseRequirements" type="password" id="password"
                        autocomplete="new-password" required aria-required="true" oninput="checkPassphraseStrength()">
                    <button class="aif-password-container__button" type="button" id="toggle-password"
                        data-target="password" aria-label="Afficher ou masquer le mot de passe">
                        Afficher
                    </button>
                </div>
                <?php
            aif_include_partial('info-message', [
                'id' => 'passwordHelp',
                'content' => 'Exemple : Mon@MotDePasse123']);
?>

                <div id="password-error-too-weak" class="aif-text-red aif-hide">Le mot de passe est trop faible</div>

                <div id="passphraseRequirements">
                    <p class=" aif-m0 aif-mt1w ">Votre mot de passe doit </p>
                    <ul>
                        <li id=" length">Doit contenir au moins 6 caractères</li>
                        <li id="uppercase">Doit contenir au moins une lettre majuscule</li>
                        <li id="lowercase">Doit contenir au moins une lettre minuscule</li>
                        <li id="number">Doit contenir au moins un chiffre</li>
                        <li id="special">Doit contenir au moins un caractère spécial (!, @, #, $, %, etc.)</li>
                    </ul>
                </div>

                <div class="aif-password-container">
                    <label class="aif-password-container__label" for="password">Nouveau mot de passe (obligatoire)</label>
                    <div class="aif-password-container__input-wrapper">
                        <input class="aif-password-container__input aif-input" name="confirm-password"
                            aria-describedby="passwordHelp passphraseRequirements" type="password" id="confirm-password"
                            autocomplete="new-password" required aria-required="true" oninput="checkPasswordMatch()">
                        <button class="aif-password-container__button" type="button" id="toggle-password"
                            data-target="confirm-password" aria-label="Afficher ou masquer le mot de passe">
                            Afficher
                        </button>
                    </div>
                    <div id="password-error-not-match" class="aif-text-red aif-hide">Les mots de passe ne correspondent pas.</div>
                    <button class="btn aif-mt1w aif-button--full" type="submit" id="submit-btn">Réinitialiser mon mot de passe</button>
                </div>
            </div>
        </form>
    </section>
</main>

<?php get_footer(); ?>
